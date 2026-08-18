<?php

declare(strict_types=1);

namespace MCF\Queue;

use Illuminate\Contracts\Queue\Queue;
use Illuminate\Queue\QueueManager;
use MCF\Queue\McfQueueRuntime;

final class McfQueueManager
{
    private bool $registered = false;

    public function __construct(
        private readonly QueueManager $manager,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    */

    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        $driver = $this->manager->getDefaultDriver();

        /*
        |--------------------------------------------------------------------------
        | Resolve Original Connection
        |--------------------------------------------------------------------------
        |
        | Resolve the original Laravel queue connection before registering
        | the MCF resolver. This gives MCF the real Laravel connection that
        | will remain responsible for queue storage and processing.
        |
        */

        $original = $this->manager->connection(
            $driver,
        );

        /*
        |--------------------------------------------------------------------------
        | Register MCF Resolver
        |--------------------------------------------------------------------------
        |
        | Keep the original driver name.
        |
        | Example:
        |
        | QUEUE_CONNECTION=database
        |
        | database
        |     ↓
        | MCF Queue Connection
        |     ↓
        | Laravel Database Queue
        |
        */

        $this->manager->extend(
            $driver,
            static function () use ($original): Queue {
                return new McfQueueConnection(
                    connection: $original,
                    onQueued: static function (): void {
                        McfQueueRuntime::wake();
                    },
                );
            },
        );

        /*
        |--------------------------------------------------------------------------
        | Clear Resolved Connections
        |--------------------------------------------------------------------------
        |
        | Laravel already resolved the original connection above. Forget the
        | resolved drivers so the next connection() call goes through the
        | MCF resolver registered above.
        |
        */

        $this->manager->forgetDrivers();

        $this->registered = true;
    }
}
