<?php

declare(strict_types=1);

namespace App\MCF\AccessControl\Data;

use App\MCF\AccessControl\Enum\GuardType;

final readonly class AuthRouteAccess extends RouteAccess
{
    /**
     * @param string[] $routeNames
     * @param string[] $permissions
     */
    public function __construct(
        array $routeNames,
        public string $access = 'all',
        public array $permissions = [],
    ) {
        parent::__construct(
            routeNames: $routeNames,
            guard: GuardType::AUTH,
        );
    }
}
