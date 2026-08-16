<?php

declare(strict_types=1);

namespace App\MCF\Audit\Internal;

use App\MCF\Audit\Data\AuditDefinition;
use Illuminate\Database\Eloquent\Model;

final class AuditMatcher
{
    public function __construct(
        private AuditConditionEvaluator $conditionEvaluator,
    ) {
    }

    /**
     * Determine whether an Audit Definition matches
     * the current model event.
     *
     * @param string[] $changedColumns
     */
    public function matches(
        Model $model,
        AuditDefinition $definition,
        string $action,
        array $changedColumns = [],
    ): bool {
        if ($definition->action !== $action) {
            return false;
        }

        /*
         * Columns are only relevant to UPDATE.
         *
         * CREATE and DELETE skip the columns check
         * completely and continue directly to the condition.
         */
        if ($action === 'update') {
            if (! $this->matchesColumns(
                $definition->columns,
                $changedColumns,
            )) {
                return false;
            }
        }

        return $this->conditionEvaluator->passes(
            $model,
            $definition->condition,
        );
    }

    /**
     * Determine whether the configured columns
     * match the columns affected by the update.
     *
     * An empty columns list means that the update
     * is not restricted to specific columns.
     *
     * The "any" value also means that any changed
     * column is accepted.
     *
     * @param string[] $configuredColumns
     * @param string[] $changedColumns
     */
    private function matchesColumns(
        array $configuredColumns,
        array $changedColumns,
    ): bool {
        if ($configuredColumns === []) {
            return true;
        }

        if (in_array(
            'any',
            $configuredColumns,
            true,
        )) {
            return true;
        }

        foreach ($configuredColumns as $column) {
            if (in_array(
                $column,
                $changedColumns,
                true,
            )) {
                return true;
            }
        }

        return false;
    }
}
