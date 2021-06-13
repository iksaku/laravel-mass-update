<?php

namespace iksaku\Laravel\MassUpdate;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * @mixin Model
 */
trait MassUpdatable
{
    public function scopeMassUpdate(Builder $query, array $values, array | string | null $uniqueBy = null): int
    {
        if (empty($values)) {
            return 0;
        }

        if ($uniqueBy !== null && empty($uniqueBy)) {
            return 0;
        }

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
            /*
             * List of conditions for our future `CASE` statement to be met
             * in order to update current record's value.
             */
            $preCompiledConditions = [];

            /*
             * Loop through columns designed as `unique`, which will allow
             * the DB to properly assign the correct value to the correct
             * record.
             */
            foreach (array_intersect_key($record, $intersectionColumns) as $column => $value) {
                $preCompiledConditions[] = "{$query->getGrammar()->wrap($column)} = $value";

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
            foreach (array_diff_key($record, $intersectionColumns) as $column => $value) {
                $preCompiledAssociation = "WHEN $preCompiledConditions THEN $value";

                if (! isset($preCompiledUpdateStatements[$column])) {
                    $preCompiledUpdateStatements[$column] = [$preCompiledAssociation];

                    continue;
                }

                if (! in_array($preCompiledAssociation, $preCompiledUpdateStatements[$column])) {
                    $preCompiledUpdateStatements[$column][] = $preCompiledAssociation;
                }
            }
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
        $compiledUpdateStatements = array_map(
            function (array $conditionalAssignments, string $column) use ($query) {
                $conditions = implode("\n", $conditionalAssignments);

                return DB::raw(<<<SQL
                    CASE $conditions
                    ELSE {$query->getGrammar()->wrap($column)}
                    END
                SQL);
            },
            $preCompiledUpdateStatements,
            array_keys($preCompiledUpdateStatements)
        );

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
