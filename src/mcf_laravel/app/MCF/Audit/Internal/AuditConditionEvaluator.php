<?php

declare(strict_types=1);

namespace App\MCF\Audit\Internal;

use Illuminate\Database\Eloquent\Model;

final class AuditConditionEvaluator
{
    /**
     * Determine whether the model satisfies
     * the configured audit condition.
     *
     * @param array<string, mixed>|null $condition
     */
    public function passes(
        Model $model,
        ?array $condition,
    ): bool {
        if (
            $condition === null
            || $condition === []
        ) {
            return true;
        }

        foreach ($condition as $column => $expectedValue) {
            if (
                $model->getAttribute($column) !== $expectedValue
            ) {
                return false;
            }
        }

        return true;
    }
}
