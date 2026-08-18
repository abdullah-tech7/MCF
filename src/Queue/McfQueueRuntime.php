<?php

declare(strict_types=1);

namespace MCF\Queue;

use Illuminate\Support\Facades\Cache;
use RuntimeException;

final class McfQueueRuntime
{
    private const WORKER_KEY = 'mcf:queue:worker:running';

    private const WORKER_TTL = 120;

    private function __construct()
    {
    }

    /*
    |--------------------------------------------------------------------------
    | Wake
    |--------------------------------------------------------------------------
    */

    public static function wake(): void
    {
        if (! self::isEnabled()) {
            return;
        }

        if (self::isWorkerRunning()) {
            return;
        }

        $lock = McfQueueLock::acquire();

        if ($lock === null) {
            return;
        }

        try {
            if (self::isWorkerRunning()) {
                return;
            }

            if (! McfQueueWorker::start()) {
                throw new RuntimeException(
                    'MCF Queue Worker could not be started.',
                );
            }

            self::markWorkerAsRunning();
        } finally {
            McfQueueLock::release($lock);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Worker State
    |--------------------------------------------------------------------------
    */

    public static function isWorkerRunning(): bool
    {
        return Cache::has(
            self::WORKER_KEY,
        );
    }

    public static function markWorkerAsRunning(): void
    {
        Cache::put(
            self::WORKER_KEY,
            true,
            self::WORKER_TTL,
        );
    }

    public static function refreshWorkerState(): void
    {
        Cache::put(
            self::WORKER_KEY,
            true,
            self::WORKER_TTL,
        );
    }

    public static function clearWorkerState(): void
    {
        Cache::forget(
            self::WORKER_KEY,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */

    private static function isEnabled(): bool
    {
        return (bool) config(
            'mcf.queue.auto',
            true,
        );
    }
}