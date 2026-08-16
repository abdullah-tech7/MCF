<?php

declare (strict_types = 1);

namespace App\MCF\Audit;

use App\MCF\Authentication\UserSettings;
use App\Models\AuditLog;
use Illuminate\Contracts\Auth\Authenticatable;

final class AuditSettings
{

    /**
     * Enable MCF Audit globally.
     *
     * When disabled, no Audit records or Audit notifications
     * will be processed.
     */
    public static bool $enabled = true;

    /**
     * Enable authentication auditing.
     */
    public static bool $authentication = true;


    /**
     * Enable account auditing.
     */
    public static bool $account = true;

    /**
     * Returns the Audit Log model used by MCF Audit.
     */
    public static function auditModel(): string
    {
        return AuditLog::class;
    }

    public static function resolveRole(
        Authenticatable $user,
    ): int | string | null {
        return UserSettings::resolveRole($user);
    }

    /**
     * Columns whose values must not be stored
     * in Audit Log data.
     *
     * Format:
     *
     * [
     *     'users' => [
     *         'password',
     *         'remember_token',
     *     ],
     * ]
     */
    public static array $excludedDataColumns = [
        'users' => [
            'password',
            'remember_token',
        ],
    ];
}
