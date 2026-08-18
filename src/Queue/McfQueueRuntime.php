<?php

declare(strict_types=1);

namespace MCF\Queue;

final class McfQueueRuntime
{
    private function __construct()
    {
    }

    public static function wake(): void
    {
        if (! (bool) config('mcf.queue.auto', true)) {
            return;
        }

        McfQueueWorker::start();
    }
}