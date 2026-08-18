<?php

declare(strict_types=1);

namespace MCF\Queue;

use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\Event;

final class McfQueueManager
{
    public function register(): void
    {
        Event::listen(
            JobQueued::class,
            static function (JobQueued $event): void {
                McfQueueRuntime::wake();
            },
        );
    }
}