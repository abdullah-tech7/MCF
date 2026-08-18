<?php

declare(strict_types=1);

namespace MCF\Queue;

use Illuminate\Queue\Connectors\DatabaseConnector;
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

        $this->manager->addConnector(
            'database',
            function ($app) {
                $config = $app['config']->get(
                    'queue.connections.database',
                );

                $connector = new DatabaseConnector(
                    $app['db'],
                );

                $connection = $connector->connect(
                    $config,
                );

                return new McfQueueConnection(
                    connection: $connection,
                );
            },
        );
    }
}