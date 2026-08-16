<?php

declare(strict_types=1);

namespace App\MCF\AccessControl\Registry;

use App\MCF\AccessControl\Data\RoleRouteAccess;
use App\MCF\AccessControl\Data\RouteAccess;
use LogicException;

final class McfRouteDataRegistry
{
    /**
     * @var array<string, RouteAccess>
     */
    private static array $routes = [];

    /**
     * Register route access definitions.
     *
     * @param RouteAccess[] $dataRouteList
     */
    public static function register(array $dataRouteList): void
    {
        foreach ($dataRouteList as $routeAccess) {

            foreach ($routeAccess->routeNames as $routeName) {

                if (isset(self::$routes[$routeName])) {
                    throw new LogicException(
                        "Route [{$routeName}] has already been registered."
                    );
                }

                self::$routes[$routeName] = $routeAccess;

                if ($routeAccess instanceof RoleRouteAccess) {
                    foreach ($routeAccess->roles as $roleData) {
                        McfRoleDataRegistry::register(
                            $routeName,
                            $roleData,
                        );
                    }
                }
            }
        }
    }

    public static function get(string $routeName): ?RouteAccess
    {
        return self::$routes[$routeName] ?? null;
    }

    /**
     * @return array<string, RouteAccess>
     */
    public static function all(): array
    {
        return self::$routes;
    }
}
