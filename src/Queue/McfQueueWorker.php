<?php

declare(strict_types=1);

namespace MCF\Queue;

final class McfQueueWorker
{
    private function __construct()
    {
    }

    public static function start(): bool
    {
        $php = escapeshellarg(
            PHP_BINARY,
        );

        $artisan = escapeshellarg(
            base_path('artisan'),
        );

        $command = sprintf(
            '%s %s queue:work --stop-when-empty',
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
                'start "" /B cmd /C "%s"',
                $command,
            );

            exec(
                $command,
                $output,
                $status,
            );

            return $status === 0;
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