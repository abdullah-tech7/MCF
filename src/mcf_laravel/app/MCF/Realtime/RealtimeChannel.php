<?php

declare(strict_types=1);

namespace App\MCF\Realtime;

use App\MCF\Notification\McfNotification;
use App\MCF\Realtime\Internal\RealtimeRegistry;

final class RealtimeChannel
{
    private function __construct()
    {
    }

    /**
     * Register MCF realtime channels.
     *
     * Add application realtime channels here.
     */
    public static function register(): void
    {
        RealtimeRegistry::register(
            name: 'notifications',
            state: static fn (): array =>
                McfNotification::notify()->readState(),
        );
    }
}
