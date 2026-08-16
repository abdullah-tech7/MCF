<?php

declare(strict_types=1);

namespace App\MCF\Audit;

use App\MCF\Authentication\McfAuth;
use App\Models\AuditLog;

final class McfAuthAudit
{
    private function __construct()
    {
    }

    /**
     * Record an authentication audit.
     *
     * Authentication auditing can be disabled
     * through AuditSettings.
     */
    public static function record(
        string $action,
        ?string $description = null,
    ): void {
        if (! AuditSettings::$authentication) {
            return;
        }

        $user = McfAuth::user();

        AuditLog::create([
            'user_id' => $user?->getAuthIdentifier(),

            'user_role' => $user !== null
                ? AuditSettings::resolveRole($user)
                : null,

            'route_name' => request()
                ->route()
                ?->getName(),

            'action' => $action,

            'description' => $description,

            'data' => null,

            'ip_address' => request()->ip(),

            'user_agent' => request()->userAgent(),

            'created_at' => now(),
        ]);
    }
}
