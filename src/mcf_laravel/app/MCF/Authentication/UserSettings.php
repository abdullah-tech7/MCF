<?php

declare(strict_types=1);

namespace App\MCF\Authentication;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

final class UserSettings
{
    /*
     |--------------------------------------------------------------------------
     | User Model
     |--------------------------------------------------------------------------
     */

    /**
     * Returns the User model used by MCF Authentication.
     */
    public static function model(): string
    {
        return User::class;
    }

    /*
     |--------------------------------------------------------------------------
     | User Identification
     |--------------------------------------------------------------------------
     */

    /**
     * Columns that can be used to identify the user during login.
     */
    public static array $loginColumns = [
        'email',
    ];

    /*
     |--------------------------------------------------------------------------
     | User Password
     |--------------------------------------------------------------------------
     */

    /**
     * Password column used by the User model.
     */
    public static string $passwordColumn = 'password';


    /**
     * Resolve the user's role.
     *
     * Customize this method if the project uses
     * a different role structure.
     *
     * Return null if the user model does not have
     * a role column or role relationship.
     */
    public static function resolveRole(
        Authenticatable $user,
    ): int|string|null {
        return $user->role_id ?? null;
    }


    /*
     |--------------------------------------------------------------------------
     | User Role
     |--------------------------------------------------------------------------
     */

    /**
     * The role identifier reserved for the system administrator.
     *
     * This may be an integer ID or a string identifier,
     * depending on the application's role structure.
     *
     * Set to null if the application does not have
     * a system administrator role.
     */
    public static int|string|null $administratorRole = 1;

    /*
     |--------------------------------------------------------------------------
     | User Status
     |--------------------------------------------------------------------------
     */

    /**
     * Determine whether the user account is active.
     *
     * Customize this method if the project uses
     * a different account status structure.
     */
    public static function isActive(
        object $user,
    ): bool {
        return $user->is_active === true;
    }

    /**
     * Determine whether the user account is disabled.
     *
     * This is the inverse of isActive().
     */
    public static function isDisabled(
        object $user,
    ): bool {
        return ! self::isActive($user);
    }


    public static function resolveLoginRouteName(): string
    {
        return 'user.auth.login';
    }



}
