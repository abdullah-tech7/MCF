<?php

declare(strict_types=1);

namespace App\MCF\Authentication\Internal;

use App\MCF\Authentication\UserSettings;

final class PasswordService
{
    public static function updatePassword(
        object $user,
        string $password,
    ): bool {
        $passwordColumn = UserSettings::$passwordColumn;

        $user->{$passwordColumn} = HashService::make($password);

        return $user->save();
    }
}
