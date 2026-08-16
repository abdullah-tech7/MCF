<?php

declare(strict_types=1);

namespace App\MCF\Notification;

use App\MCF\Authentication\UserSettings;
use App\MCF\Mail\Notification\NotificationMail;
use Illuminate\Contracts\Auth\Authenticatable;

final class NotificationSettings
{

/**
 * User model used by the Notification system.
 *
 * Delegates the User model configuration to UserSettings.
 */
public static function userModel(): string
{
    return UserSettings::model();
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
     * Mail class used for Notification emails.
     */
    public static string $notificationMail = NotificationMail::class;
}
