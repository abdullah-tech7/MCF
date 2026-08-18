<?php

declare(strict_types=1);

namespace MCF\Queue;

use Illuminate\Contracts\Queue\Queue;
use Illuminate\Queue\QueueManager;

final class McfQueueManager
{
    private bool $registered = false;

    public function __construct(
        private readonly QueueManager $manager,
    ) {
    }

    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        $this->registered = true;

        $driver = $this->manager->getDefaultDriver();

        /*
        |--------------------------------------------------------------------------
        | Resolve Original Connection
        |--------------------------------------------------------------------------
        |
        | Resolve Laravel's original connection first.
        |
        */

        $original = $this->manager->connection(
            $driver,
        );

        /*
        |--------------------------------------------------------------------------
        | Register MCF Connection
        |--------------------------------------------------------------------------
        */

        $this->manager->extend(
            $driver,
            static function () use ($original): Queue {
                return new McfQueueConnection(
                    connection: $original,
                );
            },
        );
    }
}