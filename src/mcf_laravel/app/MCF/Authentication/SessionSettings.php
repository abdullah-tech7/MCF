<?php

declare(strict_types=1);

namespace App\MCF\Authentication;

use Illuminate\Contracts\Auth\Authenticatable;

final class SessionSettings
{
    /**
     * Determines whether the user may have multiple active sessions.
     *
     * false = only one active session is allowed.
     * true  = multiple active sessions are allowed.
     */
    public static bool $multipleSessionsPerUser = true;

    /*
     |--------------------------------------------------------------------------
     | Session Security
     |--------------------------------------------------------------------------
     */

    /**
     * Enable or disable Session Security.
     *
     * false = disabled.
     * true  = enabled.
     */
    public static bool $securityEnabled = false;

    /**
     * Session timeout in seconds.
     */
    public static int $securityTimeoutSeconds = 300; // 5 minutes

    /**
     * Determines whether the session timeout is reset by user activity.
     *
     * false = timeout is not reset by activity.
     * true  = timeout is reset by activity.
     */
    public static bool $timeoutResetOnActivity = true;

    /**
     * Resolve the route name used for authentication.
     */
    public static function resolveLoginRouteName(): string
    {
        return UserSettings::resolveLoginRouteName();
    }

    /**
     * Resolve the authenticated user's role.
     *
     * Delegates the User role structure to UserSettings.
     */
    public static function resolveRole(
        Authenticatable $user,
    ): int|string|null {
        return UserSettings::resolveRole($user);
    }

    /**
     * Defines who is covered by Session Security.
     *
     * all   = all authenticated users.
     * roles = only the specified roles.
     */
    public static string $securityScope = 'roles';

    /**
     * Resolve the roles covered by Session Security.
     *
     * The administrator role is included automatically.
     */
    public static function securityRoles(): array
    {
        $roles = [];

        $administratorRole = UserSettings::$administratorRole;

        if ($administratorRole !== null) {
            $roles[] = $administratorRole;
        }

        return $roles;
    }
}
