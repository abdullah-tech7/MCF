<?php

declare(strict_types=1);

namespace App\MCF\Authentication;

use App\MCF\Authentication\Internal\Enum\VerificationRequirement;

final class AuthenticationSettings
{
    /*
     |--------------------------------------------------------------------------
     | User Authentication
     |--------------------------------------------------------------------------
     */

    /**
     * Determines whether the user is allowed to authenticate.
     *
     * Customize this method if the project requires
     * additional authentication conditions.
     */
    public static function canAuthenticate(
        object $user,
    ): bool {
        return UserSettings::isActive($user);
    }

    /*
     |--------------------------------------------------------------------------
     | Verification
     |--------------------------------------------------------------------------
     */

    /**
     * Defines whether authentication requires verification.
     *
     * VerificationRequirement::NONE
     * VerificationRequirement::EMAIL
     * VerificationRequirement::PHONE
     */
    public static VerificationRequirement $verificationRequirement =
        VerificationRequirement::NONE;

    /*
     |--------------------------------------------------------------------------
     | Login Throttle
     |--------------------------------------------------------------------------
     */

    public static int $loginMaxAttempts = 5;

    public static int $loginLockoutSeconds = 900;

    public static int $loginIpMaxAttempts = 30;

    public static int $loginIpLockoutSeconds = 900;

    /*
     |--------------------------------------------------------------------------
     | Verification
     |--------------------------------------------------------------------------
     */

    public static function verificationRequestModel(): string
    {
        return \App\Models\VerificationRequest::class;
    }

    public static int $verificationCodeExpirationSeconds = 600;

    /*
     |--------------------------------------------------------------------------
     | Verification Throttle
     |--------------------------------------------------------------------------
     */

    public static int $verificationCooldownSeconds = 60;

    public static int $verificationMaxSendAttempts = 5;

    public static int $verificationLockoutSeconds = 3600;
}
