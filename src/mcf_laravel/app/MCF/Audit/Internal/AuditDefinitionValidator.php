<?php

declare(strict_types=1);

namespace App\MCF\Audit\Internal;

use App\MCF\Audit\Data\AuditDefinition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use LogicException;

final class AuditDefinitionValidator
{
    /**
     * Validate an Audit Definition for the given model.
     *
     * @throws LogicException
     */
    public function validate(
        Model $model,
        AuditDefinition $definition,
    ): void {
        $this->validateAction(
            $definition->action,
        );

        $table = $model->getTable();

        $columns = Schema::getColumnListing(
            $table,
        );

        $this->validateColumns(
            $definition->columns,
            $columns,
            $model,
            'columns',
        );

        $this->validateColumns(
            $this->conditionColumns(
                $definition->condition,
            ),
            $columns,
            $model,
            'condition',
        );
    }

    /**
     * Validate the configured Audit action.
     *
     * @throws LogicException
     */
    private function validateAction(
        string $action,
    ): void {
        if (
            ! in_array(
                strtolower(trim($action)),
                [
                    'create',
                    'update',
                    'delete',
                ],
                true,
            )
        ) {
            throw new LogicException(
                sprintf(
                    'Invalid audit action [%s]. Allowed actions are: create, update, delete.',
                    $action,
                ),
            );
        }
    }

    /**
     * Validate configured columns.
     *
     * @param string[] $configuredColumns
     * @param string[] $databaseColumns
     *
     * @throws LogicException
     */
    private function validateColumns(
        array $configuredColumns,
        array $databaseColumns,
        Model $model,
        string $source,
    ): void {
        foreach ($configuredColumns as $column) {

            if ($column === 'any') {
                continue;
            }

            if (
                ! in_array(
                    $column,
                    $databaseColumns,
                    true,
                )
            ) {
                throw new LogicException(
                    sprintf(
                        'Audit %s column [%s] does not exist in table [%s] for model [%s].',
                        $source,
                        $column,
                        $model->getTable(),
                        $model::class,
                    ),
                );
            }
        }
    }

    /**
     * Extract column names from the condition.
     *
     * @param array<string, mixed>|null $condition
     *
     * @return string[]
     */
    private function conditionColumns(
        ?array $condition,
    ): array {
        if (
            $condition === null
            || $condition === []
        ) {
            return [];
        }

        return array_keys($condition);
    }
}
