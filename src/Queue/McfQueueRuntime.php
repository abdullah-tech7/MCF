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
        if (!self::isEnabled()) {
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
            /*
            | Re-check after acquiring the lock.
            |
            | Another request may have started the worker while this
            | request was waiting for the lock.
            */

            if (self::isWorkerRunning()) {
                return;
            }

            self::markWorkerAsRunning();

            if (!self::startWorker()) {
                self::clearWorkerState();

                throw new RuntimeException(
                    'MCF Queue Worker could not be started.',
                );
            }
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

    /*
    |--------------------------------------------------------------------------
    | Worker
    |--------------------------------------------------------------------------
    */

    private static function startWorker(): bool
    {
        $php = escapeshellarg(
            PHP_BINARY,
        );

        $artisan = escapeshellarg(
            base_path('artisan'),
        );

        $command = sprintf(
            '%s %s mcf:queue:work',
            $php,
            $artisan,
        );

        return self::runInBackground(
            $command,
        );
    }

    private static function runInBackground(
        string $command,
    ): bool {
        if (PHP_OS_FAMILY === 'Windows') {
            $command = sprintf(
                'start "" /B %s',
                $command,
            );

            $process = popen(
                $command,
                'r',
            );

            if ($process === false) {
                return false;
            }

            pclose($process);

            return true;
        }

        $command = sprintf(
            'nohup %s > /dev/null 2>&1 &',
            $command,
        );

        exec(
            $command,
            $output,
            $status,
        );

        return $status === 0;
    }
}
