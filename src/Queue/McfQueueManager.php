<?php

declare(strict_types=1);

namespace MCF\Queue;

use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\Event;

final class McfQueueManager
{
    private function __construct()
    {
    }

    public static function register(): void
    {
        Event::listen(
            JobQueued::class,
            static function (): void {
                McfQueueRuntime::wake();
            },
        );
    }
}