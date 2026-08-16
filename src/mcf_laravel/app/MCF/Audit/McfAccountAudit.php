<?php

declare(strict_types=1);

namespace App\MCF\Audit;

use App\MCF\Authentication\McfAuth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use App\Models\AuditLog;


final class McfAccountAudit
{
    private function __construct()
    {
    }

    /**
     * Record an account audit.
     *
     * Account auditing can be disabled
     * through AuditSettings.
     */
    public static function record(
        string $action,
        Authenticatable|Model $target,
        ?string $description = null,
    ): void {
        if (! AuditSettings::$account) {
            return;
        }

        $actor = McfAuth::user();

        AuditLog::create([
            'user_id' => $actor?->getAuthIdentifier(),

            'user_role' => $actor !== null
                ? AuditSettings::resolveRole($actor)
                : null,

            'route_name' => request()
                ->route()
                ?->getName(),

            'action' => $action,

            'description' => $description,

            'data' => [
                'target_user_id' => $target->getAuthIdentifier(),
            ],

            'ip_address' => request()->ip(),

            'user_agent' => request()->userAgent(),

            'created_at' => now(),
        ]);
    }
}
