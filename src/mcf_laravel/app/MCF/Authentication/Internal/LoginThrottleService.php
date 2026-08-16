<?php

declare(strict_types=1);

namespace App\MCF\Authentication\Internal;

use App\MCF\Authentication\AuthenticationSettings;
use App\MCF\Authentication\UserSettings;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Auth\Authenticatable;

final class LoginThrottleService
{
    private const ACCOUNT_PREFIX = 'mcf:auth:login:account';

    private const ACCOUNT_IP_PREFIX = 'mcf:auth:login:account_ip';

    private const IP_PREFIX = 'mcf:auth:login:ip';

    private function __construct()
    {
    }

    /*
     |--------------------------------------------------------------------------
     | Throttle Check
     |--------------------------------------------------------------------------
     */

    public static function isThrottled(
        ?string $identifier = null,
    ): bool {
        $limiter = self::limiter();

        if (
            $identifier !== null
            && $limiter->tooManyAttempts(
                self::accountIpKey($identifier),
                AuthenticationSettings::$loginMaxAttempts,
            )
        ) {
            return true;
        }

        if (
            $identifier !== null
            && $limiter->tooManyAttempts(
                self::accountKey($identifier),
                AuthenticationSettings::$loginMaxAttempts,
            )
        ) {
            return true;
        }

        return $limiter->tooManyAttempts(
            self::ipKey(),
            AuthenticationSettings::$loginIpMaxAttempts,
        );
    }

    /*
     |--------------------------------------------------------------------------
     | Record Attempt
     |--------------------------------------------------------------------------
     */

    public static function hit(
        ?string $identifier = null,
    ): void {
        $limiter = self::limiter();

        if ($identifier !== null) {
            $limiter->hit(
                self::accountIpKey($identifier),
                AuthenticationSettings::$loginLockoutSeconds,
            );

            $limiter->hit(
                self::accountKey($identifier),
                AuthenticationSettings::$loginLockoutSeconds,
            );
        }

        $limiter->hit(
            self::ipKey(),
            AuthenticationSettings::$loginIpLockoutSeconds,
        );
    }

    /*
     |--------------------------------------------------------------------------
     | Clear
     |--------------------------------------------------------------------------
     */

    public static function clear(
        ?string $identifier = null,
    ): void {
        $limiter = self::limiter();

        if ($identifier !== null) {
            $limiter->clear(
                self::accountIpKey($identifier),
            );

            $limiter->clear(
                self::accountKey($identifier),
            );
        }

        $limiter->clear(
            self::ipKey(),
        );
    }

    /*
     |--------------------------------------------------------------------------
     | Available In
     |--------------------------------------------------------------------------
     */

    public static function availableIn(
        ?string $identifier = null,
    ): int {
        $limiter = self::limiter();

        $seconds = $limiter->availableIn(
            self::ipKey(),
        );

        if ($identifier !== null) {
            $seconds = max(
                $seconds,
                $limiter->availableIn(
                    self::accountKey($identifier),
                ),
                $limiter->availableIn(
                    self::accountIpKey($identifier),
                ),
            );
        }

        return $seconds;
    }

    /*
     |--------------------------------------------------------------------------
     | Identifier
     |--------------------------------------------------------------------------
     */

    public static function identifierFromCredentials(
        array $credentials,
    ): ?string {
        foreach (
            UserSettings::$loginColumns
            as $column
        ) {
            if (
                array_key_exists(
                    $column,
                    $credentials,
                )
                && $credentials[$column] !== null
                && $credentials[$column] !== ''
            ) {
                return (string) $credentials[$column];
            }
        }

        return null;
    }

    public static function identifierFromUser(
        Authenticatable $user,
    ): ?string {
        foreach (
            UserSettings::$loginColumns
            as $column
        ) {
            $value = $user->{$column} ?? null;

            if (
                $value !== null
                && $value !== ''
            ) {
                return (string) $value;
            }
        }

        return null;
    }

    /*
     |--------------------------------------------------------------------------
     | Rate Limiter
     |--------------------------------------------------------------------------
     */

    private static function limiter(): RateLimiter
    {
        return app(RateLimiter::class);
    }

    /*
     |--------------------------------------------------------------------------
     | Keys
     |--------------------------------------------------------------------------
     */

    private static function accountKey(
        string $identifier,
    ): string {
        return self::ACCOUNT_PREFIX
            . ':'
            . self::hashIdentifier($identifier);
    }

    private static function accountIpKey(
        string $identifier,
    ): string {
        return self::ACCOUNT_IP_PREFIX
            . ':'
            . self::hashIdentifier($identifier)
            . ':'
            . self::ip();
    }

    private static function ipKey(): string
    {
        return self::IP_PREFIX
            . ':'
            . self::ip();
    }

    private static function hashIdentifier(
        string $identifier,
    ): string {
        return hash(
            'sha256',
            $identifier,
        );
    }

    private static function ip(): string
    {
        return request()->ip() ?? 'unknown';
    }
}
