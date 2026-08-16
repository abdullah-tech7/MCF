<?php

declare(strict_types=1);

namespace App\MCF\AccessControl\Registry;

use App\MCF\AccessControl\Data\RoleData;
use RuntimeException;

final class McfRoleDataRegistry
{
    /**
     * @var array<string, array<int|string, RoleData>>
     */
    private static array $roles = [];

    /**
     * Register role data for a specific route.
     *
     * @throws RuntimeException
     */
    public static function register(
        string $routeName,
        RoleData $roleData,
    ): void {
        if (isset(self::$roles[$routeName][$roleData->role])) {
            throw new RuntimeException(
                sprintf(
                    'Role [%s] is already registered for route [%s].',
                    (string) $roleData->role,
                    $routeName,
                ),
            );
        }

        self::$roles[$routeName][$roleData->role] = $roleData;
    }

    public static function get(
        string $routeName,
        int|string $role,
    ): ?RoleData {
        return self::$roles[$routeName][$role] ?? null;
    }

    /**
     * @return array<int|string, RoleData>
     */
    public static function allForRoute(string $routeName): array
    {
        return self::$roles[$routeName] ?? [];
    }

    /**
     * @return array<string, array<int|string, RoleData>>
     */
    public static function all(): array
    {
        return self::$roles;
    }
}
