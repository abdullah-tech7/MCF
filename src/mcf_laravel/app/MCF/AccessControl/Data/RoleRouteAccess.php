<?php

declare(strict_types=1);

namespace App\MCF\AccessControl\Data;

use App\MCF\AccessControl\Enum\GuardType;

final readonly class RoleRouteAccess extends RouteAccess
{
    /**
     * @param string[] $routeNames
     * @param RoleData[] $roles
     */
    public function __construct(
        array $routeNames,
        public array $roles,
    ) {
        parent::__construct(
            routeNames: $routeNames,
            guard: GuardType::ROLE,
        );
    }
}
