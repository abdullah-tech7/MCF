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

    dd($command);
}
}