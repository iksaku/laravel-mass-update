<?php

namespace Iksaku\Laravel\MassUpdate;

use Iksaku\Laravel\MassUpdate\Exceptions\EmptyUniqueByException;
use Iksaku\Laravel\MassUpdate\Exceptions\MassUpdatingAndFilteringModelUsingTheSameColumn;
use Iksaku\Laravel\MassUpdate\Exceptions\MissingFilterableColumnsException;
use Iksaku\Laravel\MassUpdate\Exceptions\OrphanValueException;
use Iksaku\Laravel\MassUpdate\Exceptions\RecordWithoutFilterableColumnsException;
use Iksaku\Laravel\MassUpdate\Exceptions\RecordWithoutUpdatableValuesException;
use Iksaku\Laravel\MassUpdate\Exceptions\UnexpectedModelClassException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * @mixin Model
 */
trait MassUpdatable
{
    public function scopeMassUpdate(Builder $query, array | Arrayable $values, array | string | null $uniqueBy = null): int
    {
        if (empty($values)) {
            return 0;
        }

        if ($uniqueBy !== null && empty($uniqueBy)) {
            throw new EmptyUniqueByException();
        }

        $quoteIfString = fn (mixed $value) => is_string($value)
            ? $query->getGrammar()->quoteString($value)
            : $value;

        $uniqueBy = Arr::wrap($uniqueBy ?? $this->getKeyName());

        /*
         * Values per row to use as a query filter.
         * Example:
         *  [
         *      'id' => [1, 2, 3, 4, ...],
         *      ...
         *  ]
         */
        $whereIn = [];

        /*
         * Column name-value association pending update.
         * Value is pre-compiled into a `WHEN <condition> THEN <value>` format.
         * Example:
         *  [
         *      'name' => [
         *          'WHEN `id` = 1 THEN Jorge González',
         *          'WHEN `id` = 2 THEN Elena González',
         *          ...
         *      ]
         *  ]
         */
        $preCompiledUpdateStatements = [];

        // Cached column reference used to separate from each record's value.
        $intersectionColumns = array_flip($uniqueBy);

        foreach ($values as $record) {
            if (empty($record)) {
                continue;
            }

            if ($record instanceof Model) {
                if ($record::class !== static::class) {
                    throw new UnexpectedModelClassException(static::class, $record::class);
                }

                if (! $record->isDirty()) {
                    continue;
                }

                $uniqueColumns = array_intersect_key($record->getAttributes(), $intersectionColumns);
                $updatableColumns = $record->getDirty();

                if (! empty($crossReferencedColumns = array_intersect_key($updatableColumns, $uniqueColumns))) {
                    throw new MassUpdatingAndFilteringModelUsingTheSameColumn($crossReferencedColumns);
                }
            } else {
                $uniqueColumns = array_intersect_key($record, $intersectionColumns);
                $updatableColumns = array_diff_key($record, $intersectionColumns);
            }

            if (empty($uniqueColumns)) {
                throw new RecordWithoutFilterableColumnsException();
            }

            if (empty($updatableColumns)) {
                throw new RecordWithoutUpdatableValuesException();
            }

            if (count($missingColumns = array_diff_key($intersectionColumns, $uniqueColumns)) > 0) {
                throw new MissingFilterableColumnsException(array_flip($missingColumns));
            }

            /*
             * List of conditions for our future `CASE` statement to be met
             * in order to update current record's value.
             */
            $preCompiledConditions = [];

            /*
             * Loop through columns labelled as `unique`, which will allow
             * the DB to properly assign the correct value to the correct
             * record.
             */
            foreach ($uniqueColumns as $column => $value) {
                $preCompiledConditions[] = "{$query->getGrammar()->wrap($column)} = {$quoteIfString($value)}";

                if (! isset($whereIn[$column])) {
                    $whereIn[$column] = [$value];

                    continue;
                }

                if (! in_array($value, $whereIn[$column])) {
                    $whereIn[$column][] = $value;
                }
            }

            $preCompiledConditions = implode(' AND ', $preCompiledConditions);

            /*
             * Loop through the columns that are actual values to update.
             * These do not include the `unique columns`, so we will not
             * be updating those.
             */
            foreach ($updatableColumns as $column => $value) {
                if (! is_string($column)) {
                    throw new OrphanValueException($value);
                }

                $preCompiledAssociation = "WHEN $preCompiledConditions THEN {$quoteIfString($value)}";

                if (! isset($preCompiledUpdateStatements[$column])) {
                    $preCompiledUpdateStatements[$column] = [$preCompiledAssociation];

                    continue;
                }

                if (! in_array($preCompiledAssociation, $preCompiledUpdateStatements[$column])) {
                    $preCompiledUpdateStatements[$column][] = $preCompiledAssociation;
                }
            }
        }

        if (empty($preCompiledUpdateStatements)) {
            return 0;
        }

        /*
         * Tell the DB to only operate in rows where the specified
         * `unique` columns equal the collected values.
         */
        foreach ($whereIn as $column => $values) {
            $query->whereIn($column, $values);
        }

        /*
         * Final column name-value association pending update.
         * Value is compiled as an SQL `CASE WHEN ... THEN ...` statement,
         * which will tell the DB to assign a different value depending
         * on the column values of the row it's currently operating on.
         * Example:
         *  [
         *      'name' => <<<SQL
         *          CASE WHEN `id` = 1 THEN Jorge González
         *               WHEN `id` = 2 THEN Elena González
         *               ELSE `name`
         *          END
         *      SQL,
         *      ...
         *  ]
         */
        $compiledUpdateStatements = collect($preCompiledUpdateStatements)
            ->mapWithKeys(function (array $conditionalAssignments, string $column) use ($query) {
                $conditions = implode("\n", $conditionalAssignments);

                return [
                    $column => DB::raw(<<<SQL
                        CASE $conditions
                        ELSE {$query->getGrammar()->wrap($column)}
                        END
                    SQL),
                ];
            })
            ->toArray();

        // If the model tracks an update timestamp, update it for all touched records.
        if ($this->usesTimestamps() && $this->getUpdatedAtColumn() !== null) {
            $compiledUpdateStatements[$this->getUpdatedAtColumn()] = $this->freshTimestampString();
        }

        /*
         * Finally, execute the update query against the database and
         * return the number of touched records.
         */
        return $query->update($compiledUpdateStatements);
    }
}
