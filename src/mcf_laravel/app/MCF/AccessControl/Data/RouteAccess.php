<?php

declare(strict_types=1);

namespace App\MCF\AccessControl\Data;

use App\MCF\AccessControl\Enum\GuardType;

abstract readonly class RouteAccess
{
    /**
     * @param string[] $routeNames
     */
    public function __construct(
        public array $routeNames,
        public GuardType $guard,
    ) {
    }
}
