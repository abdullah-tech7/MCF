<?php

declare(strict_types=1);

namespace App\MCF\AccessControl;

use App\MCF\AccessControl\Enum\GuardType;
use App\MCF\AccessControl\Registry\McfRoleDataRegistry;
use App\MCF\AccessControl\Registry\McfRouteDataRegistry;
use App\MCF\Authentication\McfAuth;
use Illuminate\Contracts\Auth\Authenticatable;
use App\MCF\Authentication\UserSettings;


final class McfAccess
{
    private function __construct()
    {
    }

    /**
     * Resolve the authenticated user's role.
     *
     * Customize this method if the project
     * uses a different role structure.
     */
     public static function resolveRole(
        Authenticatable $user,
    ): int | string | null {
        return UserSettings::resolveRole($user);
    }

    /**
     * Resolve the route name used for authentication.
     */
    public static function resolveLoginRouteName(): string
    {
        return UserSettings::resolveLoginRouteName();
    }

    /**
     * Check whether the current user has a permission
     * for the current route.
     */
    public static function can(string $permission): bool
    {
        if (! McfAuth::check()) {
            return false;
        }

        $user = McfAuth::user();

        if ($user === null) {
            return false;
        }

        $routeName = request()->route()?->getName();

        if ($routeName === null) {
            return false;
        }

        $routeAccess = McfRouteDataRegistry::get($routeName);

        if ($routeAccess === null) {
            return false;
        }

        /*
         * ROLE
         *
         * Resolve access using:
         *
         * Route + User Role
         */
        if ($routeAccess->guard === GuardType::ROLE) {
            $role = self::resolveRole($user);

            $roleData = McfRoleDataRegistry::get(
                $routeName,
                $role,
            );

            if ($roleData === null) {
                return false;
            }

            return self::checkAccess(
                access: $roleData->access,
                permissions: $roleData->permissions,
                permission: $permission,
            );
        }

        /*
         * NON-ROLE
         *
         * Access is defined directly on the route.
         */
        return self::checkAccess(
            access: $routeAccess->access,
            permissions: $routeAccess->permissions,
            permission: $permission,
        );
    }

    /**
     * Resolve a permission according to the access definition.
     *
     * Supported access values:
     *
     * all
     * none
     * only
     * except
     *
     * Access comparison is case-insensitive.
     *
     * Unknown access values fail safely as "none".
     */
    private static function checkAccess(
        string $access,
        array $permissions,
        string $permission,
    ): bool {
        $access = strtolower(trim($access));

        return match ($access) {
            'all' => true,

            'none' => false,

            'only' => self::hasPermission(
                permissions: $permissions,
                permission: $permission,
            ),

            'except' => ! self::hasPermission(
                permissions: $permissions,
                permission: $permission,
            ),

            default => false,
        };
    }

    /**
     * Determine whether a permission exists in the permission list.
     */
    private static function hasPermission(
        array $permissions,
        string $permission,
    ): bool {
        return in_array(
            $permission,
            $permissions,
            true,
        );
    }
}
