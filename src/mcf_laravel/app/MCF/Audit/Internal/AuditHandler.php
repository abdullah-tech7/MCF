<?php

declare(strict_types=1);

namespace App\MCF\Audit\Internal;

use App\MCF\Audit\AuditSettings;
use App\MCF\Audit\Data\AuditDefinition;
use App\MCF\Notification\Internal\NotificationRequest;
use App\MCF\Notification\McfNotification;
use App\MCF\Authentication\McfAuth;
use Illuminate\Database\Eloquent\Model;
use LogicException;

final class AuditHandler
{
    /**
     * Create an Audit Log for a matched definition.
     *
     * @param array<string, mixed>|null $data
     */
    public function record(
        Model $model,
        AuditDefinition $definition,
        ?array $data = null,
    ): void {
        $auditModel = AuditSettings::auditModel();

        $user = McfAuth::user();

        $auditModel::create([
            'user_id' => $user?->getAuthIdentifier(),

            'user_role' => $user !== null
                ? AuditSettings::resolveRole($user)
                : null,

            'route_name' => request()->route()?->getName(),

            'action' => $definition->action,

            'description' => $definition->message,

            'data' => $data,

            'ip_address' => request()->ip(),

            'user_agent' => request()->userAgent(),

            'created_at' => now(),
        ]);

        $this->sendNotification(
            model: $model,
            definition: $definition,
        );
    }

    /**
     * Send the optional notification attached
     * to the Audit definition.
     */
    private function sendNotification(
        Model $model,
        AuditDefinition $definition,
    ): void {
        if ($definition->notification === null) {
            return;
        }

        $request = ($definition->notification)(
            $model,
        );

        if (! $request instanceof NotificationRequest) {
            throw new LogicException(
                'The Audit notification callback must return a NotificationRequest.',
            );
        }

        McfNotification::send(
            $request,
        );
    }
}
