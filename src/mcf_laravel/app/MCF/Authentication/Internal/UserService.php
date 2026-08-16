<?php

declare (strict_types = 1);

namespace App\MCF\Authentication\Internal;

use App\MCF\Authentication\AuthenticationSettings;
use App\MCF\Authentication\McfAccount;
use App\MCF\Authentication\UserSettings;
use Illuminate\Database\Eloquent\Model;

final class UserService
{
    /**
     * Find a user by the configured login credentials.
     *
     * Soft-deleted users are included because authentication
     * must be able to determine the deleted account state.
     */
    public static function findUser(
        array $credentials,
    ): ?Model {
        $model = UserSettings::model();

        $query = $model::withTrashed();

        $query->where(function ($query) use ($credentials): void {
            foreach (UserSettings::$loginColumns as $column) {
                if (
                    array_key_exists($column, $credentials)
                    && $credentials[$column] !== null
                    && $credentials[$column] !== ''
                ) {
                    $query->orWhere(
                        $column,
                        $credentials[$column],
                    );
                }
            }
        });

        return $query->first();
    }

    /**
     * Find a user by ID.
     */
    public static function findUserById(
        int $userId,
    ): ?Model {
        $model = UserSettings::model();

        return $model::query()->find($userId);
    }

    /**
     * Find a user by email.
     */
    public static function findUserByEmail(
        string $email,
    ): ?Model {
        $model = UserSettings::model();

        return $model::query()
            ->where('email', $email)
            ->first();
    }

    /**
     * Find a user by phone.
     */
    public static function findUserByPhone(
        string $phone,
    ): ?Model {
        $model = UserSettings::model();

        return $model::query()
            ->where('phone', $phone)
            ->first();
    }

    public static function findUserWithTrashedByEmail(
        string $email,
    ): ?Model {
        $model = UserSettings::model();

        return $model::withTrashed()
            ->where('email', $email)
            ->first();
    }

    public static function findUserWithTrashedByPhone(
        string $email,
    ): ?Model {
        $model = UserSettings::model();

        return $model::withTrashed()
            ->where('phone', $phone)
            ->first();
    }

    /**
     * Validate a user's password.
     */
    public static function validatePassword(
        Model $user,
        string $password,
    ): bool {
        $passwordColumn = UserSettings::$passwordColumn;

        return HashService::check(
            $password,
            $user->{$passwordColumn},
        );
    }

    /**
     * Determine whether the user can authenticate.
     */
    public static function canAuthenticate(
        Model $user,
    ): bool {
        return AuthenticationSettings::canAuthenticate(
            $user,
        );
    }

    /**
     * Determine whether the user's email is verified.
     */
    public static function isEmailVerified(
        Model $user,
    ): bool {
        return isset($user->email_verified_at)
        && $user->email_verified_at !== null;
    }

    /**
     * Determine whether the user's phone is verified.
     */
    public static function isPhoneVerified(
        Model $user,
    ): bool {
        return isset($user->phone_verified_at)
        && $user->phone_verified_at !== null;
    }

    /**
     * Determine whether a self-deleted account
     * can still be restored.
     *
     * The restoration timeout configuration is owned
     * by McfAccount.
     */
    public static function isSelfRestorationAvailable(
        Model $user,
    ): bool {
        if (
            ! method_exists($user, 'trashed')
            || ! $user->trashed()
        ) {
            return false;
        }

        if (
            $user->deletion_type !== 'self'
        ) {
            return false;
        }

        /*
         * No timeout means the account can be restored
         * without an expiration date.
         */
        if (! McfAccount::$selfRestorationTimeout) {
            return true;
        }

        /*
         * A timeout is enabled, therefore an expiration
         * date must exist.
         */
        if ($user->deletion_expires_at === null) {
            return false;
        }

        return $user->deletion_expires_at->isFuture();
    }

    /**
     * Update the user's email.
     */
    public static function updateEmail(
        Model $user,
        string $email,
    ): bool {
        $user->email = $email;

        return $user->save();
    }

    /**
     * Update the user's phone.
     */
    public static function updatePhone(
        Model $user,
        string $phone,
    ): bool {
        $user->phone = $phone;

        return $user->save();
    }

    /**
     * Mark the user's email as verified.
     */
    public static function markEmailAsVerified(
        Model $user,
    ): bool {
        $user->email_verified_at = now();

        return $user->save();
    }

    /**
     * Mark the user's phone as verified.
     */
    public static function markPhoneAsVerified(
        Model $user,
    ): bool {
        $user->phone_verified_at = now();

        return $user->save();
    }

    /**
     * Update the user's password.
     */
    public static function updatePassword(
        Model $user,
        string $password,
    ): bool {
        $passwordColumn = UserSettings::$passwordColumn;

        $user->{$passwordColumn} = $password;

        return $user->save();
    }
}
