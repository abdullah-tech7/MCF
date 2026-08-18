<?php

declare(strict_types=1);

namespace MCF\Queue;

use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\Event;

final class McfQueueManager
{
    private bool $registered = false;

    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        $this->registered = true;

        Event::listen(
            JobQueued::class,
            function (JobQueued $event): void {
                McfQueueRuntime::wake();
            },
        );
    }
}