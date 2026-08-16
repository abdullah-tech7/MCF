<?php

declare(strict_types=1);

namespace App\MCF\Authentication\Internal;

use App\MCF\Authentication\AuthenticationSettings;
use App\MCF\Authentication\Internal\Enum\VerificationChannel;
use App\MCF\Authentication\Internal\Enum\VerificationMethod;
use App\MCF\Authentication\Internal\Enum\VerificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


final class VerificationService
{
    /*
     * |--------------------------------------------------------------------------
     * | Active Verification
     * |--------------------------------------------------------------------------
     */

    public static function findActiveVerificationRequest(
        VerificationType $type,
        VerificationChannel $channel,
        VerificationMethod $method,
        string $target,
    ): ?Model {
        $model = AuthenticationSettings::verificationRequestModel();

        return $model::query()
            ->where('type', $type->value)
            ->where('channel', $channel->value)
            ->where('method', $method->value)
            ->where('target', $target)
            ->whereNull('verified_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();
    }


    /*
     * |--------------------------------------------------------------------------
     * | Prepare Verification
     * |--------------------------------------------------------------------------
     */

    public static function prepareVerificationRequest(
        int $userId,
        VerificationType $type,
        VerificationChannel $channel,
        VerificationMethod $method,
        string $target,
    ): ?array {
        $model = AuthenticationSettings::verificationRequestModel();

        $request = $model::query()
            ->where('user_id', $userId)
            ->where('type', $type->value)
            ->where('channel', $channel->value)
            ->where('method', $method->value)
            ->where('target', $target)
            ->whereNull('verified_at')
            ->whereNull('revoked_at')
            ->latest('id')
            ->first();

        if ($request === null) {
            return self::createVerificationRequest(
                $userId,
                $type,
                $channel,
                $method,
                $target,
            );
        }

        if (
            $request->last_sent_at !== null
            && $request->last_sent_at
                ->addSeconds(
                    AuthenticationSettings::$verificationCooldownSeconds,
                )
                ->isFuture()
        ) {
            return null;
        }

        if (
            $request->send_attempts
            >= AuthenticationSettings::$verificationMaxSendAttempts
        ) {
            return null;
        }

        return self::refreshVerificationRequest(
            $request,
        );
    }


    /*
     * |--------------------------------------------------------------------------
     * | Create Verification
     * |--------------------------------------------------------------------------
     */

    public static function createVerificationRequest(
        int $userId,
        VerificationType $type,
        VerificationChannel $channel,
        VerificationMethod $method,
        string $target,
    ): array {
        $model = AuthenticationSettings::verificationRequestModel();

        /*
         * Revoke any previous pending request of this type.
         */
        self::revokeActiveRequestsByType(
            $userId,
            $type,
        );

        /*
         * Forgot Password is special.
         *
         * A previously verified reset request is still an authorization
         * until it expires or is consumed. When a new reset request is
         * created, the previous verified reset authorization must also
         * become invalid immediately.
         */
        if (
            $type === VerificationType::RESET_PASSWORD
        ) {
            self::revokeVerifiedResetPasswordRequests(
                $userId,
            );
        }

        $value = match ($method) {
            VerificationMethod::CODE =>
                self::generateVerificationCode(),

            VerificationMethod::LINK =>
                self::generateVerificationToken(),
        };

        $request = $model::query()->create([
            'user_id'       => $userId,
            'type'          => $type->value,
            'channel'       => $channel->value,
            'method'        => $method->value,
            'target'        => $target,

            'code_hash'     => $method === VerificationMethod::CODE
                ? HashService::make($value)
                : null,

            'token_hash'    => $method === VerificationMethod::LINK
                ? HashService::make($value)
                : null,

            'send_attempts' => 1,
            'last_sent_at'  => now(),

            'expires_at'    => now()->addSeconds(
                AuthenticationSettings::$verificationCodeExpirationSeconds,
            ),

            'verified_at'   => null,
            'revoked_at'    => null,
        ]);

        return [
            'request' => $request,
            'value'   => $value,
        ];
    }


    /*
     * |--------------------------------------------------------------------------
     * | Refresh Verification
     * |--------------------------------------------------------------------------
     */

    public static function refreshVerificationRequest(
        Model $request,
    ): array {
        $method = VerificationMethod::from(
            $request->method,
        );

        $value = match ($method) {
            VerificationMethod::CODE =>
                self::generateVerificationCode(),

            VerificationMethod::LINK =>
                self::generateVerificationToken(),
        };

        $request->update([
            'code_hash'     => $method === VerificationMethod::CODE
                ? HashService::make($value)
                : null,

            'token_hash'    => $method === VerificationMethod::LINK
                ? HashService::make($value)
                : null,

            'send_attempts' => $request->send_attempts + 1,
            'last_sent_at'  => now(),

            'expires_at'    => now()->addSeconds(
                AuthenticationSettings::$verificationCodeExpirationSeconds,
            ),

            'verified_at'   => null,
            'revoked_at'    => null,
        ]);

        return [
            'request' => $request->fresh(),
            'value'   => $value,
        ];
    }


    /*
     * |--------------------------------------------------------------------------
     * | Revoke
     * |--------------------------------------------------------------------------
     */

    public static function revokeVerificationRequest(
        Model $request,
    ): bool {
        return $request->update([
            'revoked_at' => now(),
        ]);
    }


    /*
     * |--------------------------------------------------------------------------
     * | Complete Verification
     * |--------------------------------------------------------------------------
     */

    public static function completeVerification(
        Model $request,
    ): bool {
        if (
            $request->verified_at !== null
            || $request->revoked_at !== null
            || $request->expires_at->isPast()
        ) {
            return false;
        }

        return $request->update([
            'verified_at' => now(),
        ]);
    }


    /*
     * |--------------------------------------------------------------------------
     * | Verification Values
     * |--------------------------------------------------------------------------
     */

    public static function generateVerificationCode(): string
    {
        return (string) random_int(
            100000,
            999999,
        );
    }


    public static function generateVerificationToken(): string
    {
        return Str::random(64);
    }


    /*
     * |--------------------------------------------------------------------------
     * | Validation
     * |--------------------------------------------------------------------------
     */

    public static function validateVerificationCode(
        Model $request,
        string $code,
    ): bool {
        if (
            $request->verified_at !== null
            || $request->revoked_at !== null
            || $request->expires_at->isPast()
        ) {
            return false;
        }

        return HashService::check(
            $code,
            $request->code_hash,
        );
    }


    public static function validateVerificationToken(
        Model $request,
        string $token,
    ): bool {
        if (
            $request->verified_at !== null
            || $request->revoked_at !== null
            || $request->expires_at->isPast()
        ) {
            return false;
        }

        return HashService::check(
            $token,
            $request->token_hash,
        );
    }


    /*
     * |--------------------------------------------------------------------------
     * | Revoke Pending Requests
     * |--------------------------------------------------------------------------
     */

    private static function revokeActiveRequestsByType(
        int $userId,
        VerificationType $type,
    ): void {
        $model = AuthenticationSettings::verificationRequestModel();

        $model::query()
            ->where('user_id', $userId)
            ->where('type', $type->value)
            ->whereNull('verified_at')
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
            ]);
    }


    /*
     * |--------------------------------------------------------------------------
     * | Forgot Password - Revoke Verified Requests
     * |--------------------------------------------------------------------------
     */

    private static function revokeVerifiedResetPasswordRequests(
        int $userId,
    ): void {
        $model = AuthenticationSettings::verificationRequestModel();

        $model::query()
            ->where('user_id', $userId)
            ->where(
                'type',
                VerificationType::RESET_PASSWORD->value,
            )
            ->whereNotNull('verified_at')
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
            ]);
    }


    /*
     * |--------------------------------------------------------------------------
     * | Active Verification For User
     * |--------------------------------------------------------------------------
     */

    public static function findActiveVerificationRequestForUser(
        int $userId,
        VerificationType $type,
        VerificationChannel $channel,
        VerificationMethod $method,
        string $target,
    ): ?Model {
        $model = AuthenticationSettings::verificationRequestModel();

        return $model::query()
            ->where('user_id', $userId)
            ->where('type', $type->value)
            ->where('channel', $channel->value)
            ->where('method', $method->value)
            ->where('target', $target)
            ->whereNull('verified_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();
    }


    /*
     * |--------------------------------------------------------------------------
     * | Cooldown
     * |--------------------------------------------------------------------------
     */

    public static function cooldownRemaining(
        Model $request,
    ): int {
        if ($request->last_sent_at === null) {
            return 0;
        }

        $availableAt = $request->last_sent_at->copy()->addSeconds(
            AuthenticationSettings::$verificationCooldownSeconds,
        );

        return max(
            0,
            (int) now()->diffInSeconds(
                $availableAt,
                false,
            ),
        );
    }


    /*
     * |--------------------------------------------------------------------------
     * | Latest Active Verification For User
     * |--------------------------------------------------------------------------
     */

    public static function findLatestActiveVerificationRequestForUser(
        int $userId,
        VerificationType $type,
        VerificationChannel $channel,
        VerificationMethod $method,
    ): ?Model {
        $model = AuthenticationSettings::verificationRequestModel();

        return $model::query()
            ->where('user_id', $userId)
            ->where('type', $type->value)
            ->where('channel', $channel->value)
            ->where('method', $method->value)
            ->whereNull('verified_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();
    }


    /*
     * |--------------------------------------------------------------------------
     * | Verification Request
     * |--------------------------------------------------------------------------
     */

    public static function findVerificationRequest(
        VerificationType $type,
        VerificationChannel $channel,
        VerificationMethod $method,
        string $target,
    ): ?Model {
        $model = AuthenticationSettings::verificationRequestModel();

        return $model::query()
            ->where('type', $type->value)
            ->where('channel', $channel->value)
            ->where('method', $method->value)
            ->where('target', $target)
            ->whereNull('verified_at')
            ->whereNull('revoked_at')
            ->latest('id')
            ->first();
    }


    public static function findVerificationRequestForUser(
        int $userId,
        VerificationType $type,
        VerificationChannel $channel,
        VerificationMethod $method,
        string $target,
    ): ?Model {
        $model = AuthenticationSettings::verificationRequestModel();

        return $model::query()
            ->where('user_id', $userId)
            ->where('type', $type->value)
            ->where('channel', $channel->value)
            ->where('method', $method->value)
            ->where('target', $target)
            ->whereNull('verified_at')
            ->whereNull('revoked_at')
            ->latest('id')
            ->first();
    }


    /*
     * |--------------------------------------------------------------------------
     * | Verified Verification
     * |--------------------------------------------------------------------------
     */

    public static function findVerifiedVerificationRequest(
        VerificationType $type,
        VerificationChannel $channel,
        VerificationMethod $method,
        string $target,
    ): ?Model {
        $model = AuthenticationSettings::verificationRequestModel();

        return $model::query()
            ->where('type', $type->value)
            ->where('channel', $channel->value)
            ->where('method', $method->value)
            ->where('target', $target)
            ->whereNotNull('verified_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();
    }


    /*
     * |--------------------------------------------------------------------------
     * | Forgot Password - Verified Verification
     * |--------------------------------------------------------------------------
     */

    public static function findVerifiedResetPasswordVerificationRequest(
        string $email,
    ): ?Model {
        return self::findVerifiedVerificationRequest(
            VerificationType::RESET_PASSWORD,
            VerificationChannel::EMAIL,
            VerificationMethod::CODE,
            $email,
        );
    }
}
