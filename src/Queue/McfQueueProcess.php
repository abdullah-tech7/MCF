<?php

declare(strict_types=1);

namespace MCF\Queue;

final class McfQueueProcess
{
    private function __construct()
    {
    }

    public static function start(): bool
    {
        $php = PHP_BINARY;
        $artisan = base_path('artisan');

        if (PHP_OS_FAMILY === 'Windows') {
            $command = sprintf(
                'start "" /B cmd /D /S /C ""%s" "%s" queue:work --once > NUL 2>&1"',
                $php,
                $artisan,
            );

            exec(
                $command,
                $output,
                $status,
            );

            return $status === 0;
        }

        $command = sprintf(
            'nohup %s %s queue:work --once > /dev/null 2>&1 &',
            escapeshellarg($php),
            escapeshellarg($artisan),
        );

        exec(
            $command,
            $output,
            $status,
        );

        return $status === 0;
    }
}