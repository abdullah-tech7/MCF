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
        $php = escapeshellarg(PHP_BINARY);
        $artisan = escapeshellarg(base_path('artisan'));

        $command = sprintf(
            '%s %s queue:work --stop-when-empty',
            $php,
            $artisan,
        );

        if (PHP_OS_FAMILY === 'Windows') {
            exec(
                sprintf(
                    'start "" /B cmd /C "%s"',
                    $command,
                ),
                $output,
                $status,
            );

            return $status === 0;
        }

        exec(
            sprintf(
                'nohup %s > /dev/null 2>&1 &',
                $command,
            ),
            $output,
            $status,
        );

        return $status === 0;
    }
}