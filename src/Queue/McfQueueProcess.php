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
            return self::startWindows(
                $php,
                $artisan,
            );
        }

        return self::startUnix(
            $php,
            $artisan,
        );
    }

    private static function startWindows(
        string $php,
        string $artisan,
    ): bool {
        $command = sprintf(
            'start "" /B "%s" "%s" queue:work --once',
            $php,
            $artisan,
        );

        $process = proc_open(
            $command,
            [
                0 => [
                    'file',
                    'NUL',
                    'r',
                ],
                1 => [
                    'file',
                    'NUL',
                    'w',
                ],
                2 => [
                    'file',
                    'NUL',
                    'w',
                ],
            ],
            $pipes,
        );

        if ($process === false) {
            return false;
        }

        proc_close($process);

        return true;
    }

    private static function startUnix(
        string $php,
        string $artisan,
    ): bool {
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