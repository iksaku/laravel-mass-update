<?php

namespace iksaku\Laravel;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * @mixin Model
 */
trait MassUpdatable
{
    public function scopeMassUpdate(Builder $query, array $values, array | string | null $index = null): int
    {
        if (empty($values)) {
            return 0;
        }

        if ($index === null) {
            $index = $this->getKeyName();
        }

        if (! is_array($index)) {
            $index = Arr::wrap($index);
        }

        if (empty($index)) {
            return 0;
        }

        // Filter rows by index.
        $whereIn = [];

        // Final list of values to update (Pre-Compilation).
        $updateValues = [];

        // Cache the index columns as a filter to separate them
        // from the values to update on each row.
        $intersectIndexes = array_flip($index);

        foreach ($values as $row) {
            // Obtain the indexes on which we'll compare the value.
            $indexes = array_intersect_key($row, $intersectIndexes);

            $conditions = [];

            // Include indexes in the top-level $whereIn
            // and merge as a condition to compile later on.
            foreach ($indexes as $column => $value) {
                $whereIn[$column] ??= [];

                if (! in_array($value, $whereIn[$column])) {
                    $whereIn[$column][] = $value;
                }

                $conditions[] = "{$this->escapeColumn($column)} = $value";
            }

            // Compile into SQL case conditions.
            $conditions = implode(' AND ', $conditions);

            // Separate the values to update from their indexes.
            $valuesToUpdate = array_diff_key($row, $intersectIndexes);

            // Include values in top-level $updateValues.
            foreach ($valuesToUpdate as $column => $value) {
                $value = "WHEN $conditions THEN $value";

                $updateValues[$column] ??= [];

                if (! in_array($value, $updateValues[$column])) {
                    $updateValues[$column][] = $value;
                }
            }
        }

        // Apply $whereIn filter.
        foreach ($whereIn as $column => $indexes) {
            $query->whereIn($column, $indexes);
        }

        // Compile multiple values to update.
        $compiledUpdates = [];
        foreach ($updateValues as $column => $conditionalAssignments) {
            $compiledUpdates[$column] = DB::raw(
                'CASE '.implode("\n", $conditionalAssignments)." ELSE {$this->escapeColumn($column)} END"
            );
        }

        // If model needs it, also include the updatedAt column.
        if ($this->usesTimestamps() && $this->getUpdatedAtColumn() !== null) {
            $compiledUpdates[$this->getUpdatedAtColumn()] = now()->format($this->getDateFormat());
        }

        // Finally, execute the query and return the updated rows.
        return $query->update($compiledUpdates);
    }

    protected function escapeColumn(string $column): string
    {
        if ($this->getConnection()->getDriverName() === 'pgsql') {
            return "\"$column\"";
        }

        return "`$column`";
    }
}
