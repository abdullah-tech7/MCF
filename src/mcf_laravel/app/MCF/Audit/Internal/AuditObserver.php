<?php

declare (strict_types = 1);

namespace App\MCF\Audit\Internal;

use App\MCF\Audit\McfAuditable;
use App\MCF\Audit\AuditSettings;
use Illuminate\Database\Eloquent\Model;

final class AuditObserver
{
    public function __construct(
        private AuditDefinitionValidator $validator,
        private AuditMatcher $matcher,
        private AuditHandler $handler,
    ) {
    }

    /**
     * Handle the model created event.
     */
    public function created(Model $model): void
    {
        $this->handle(
            $model,
            'create',
        );
    }

    /**
     * Handle the model updated event.
     */
    public function updated(Model $model): void
    {
        $this->handle(
            $model,
            'update',
            array_keys(
                $model->getChanges(),
            ),
        );
    }

    /**
     * Handle the model deleted event.
     */
    public function deleted(Model $model): void
    {
        $this->handle(
            $model,
            'delete',
        );
    }

    /**
     * Process the Audit definitions for the model event.
     *
     * @param string[] $changedColumns
     */
    private function handle(
        Model $model,
        string $action,
        array $changedColumns = [],
    ): void {

if (! AuditSettings::$enabled) {
    return;
}

        if (! in_array(
            McfAuditable::class,
            class_uses_recursive($model),
            true,
        )) {
            return;
        }

        $definitions = $model::auditDefinitions();

        foreach ($definitions as $definition) {

            $this->validator->validate(
                $model,
                $definition,
            );

            if (! $this->matcher->matches(
                $model,
                $definition,
                $action,
                $changedColumns,
            )) {
                continue;
            }

            $this->handler->record(
                $model,
                $definition,
                $this->resolveData(
                    $model,
                    $action,
                    $changedColumns,
                ),
            );
        }
    }

    /**
     * Resolve additional Audit data.
     *
     * @param string[] $changedColumns
     *
     * @return array<string, mixed>|null
     */
    private function resolveData(
        Model $model,
        string $action,
        array $changedColumns,
    ): ?array {
        if ($action !== 'update') {
            return null;
        }

        $excludedColumns = AuditSettings::$excludedDataColumns[
            $model->getTable()
        ] ?? [];

        $changes = [];

        foreach ($changedColumns as $column) {
            if (in_array(
                $column,
                $excludedColumns,
                true,
            )) {
                continue;
            }

            $changes[$column] = [
                'old' => $model->getOriginal($column),
                'new' => $model->getAttribute($column),
            ];
        }

        if ($changes === []) {
            return null;
        }

        return [
            'changes' => $changes,
        ];
    }
}
