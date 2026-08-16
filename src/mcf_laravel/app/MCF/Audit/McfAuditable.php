<?php

declare(strict_types=1);

namespace App\MCF\Audit;

use App\MCF\Audit\Data\AuditDefinition;

trait McfAuditable
{
    /**
     * Define the Audit rules for the model.
     *
     * @return AuditDefinition[]
     */
    abstract public static function auditDefinitions(): array;
}
