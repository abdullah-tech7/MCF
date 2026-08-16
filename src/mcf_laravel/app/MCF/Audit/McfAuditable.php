<?php

declare(strict_types=1);

namespace App\MCF\Audit;

use App\MCF\Audit\Data\AuditDefinition;
use App\MCF\Audit\Internal\AuditObserver;

trait McfAuditable
{
    /**
     * Register the Audit Observer for the model.
     */
    public static function bootMcfAuditable(): void
    {
        static::observe(
            app(AuditObserver::class),
        );
    }

    /**
     * Define the Audit rules for the model.
     *
     * @return AuditDefinition[]
     */
    abstract public static function auditDefinitions(): array;
}
