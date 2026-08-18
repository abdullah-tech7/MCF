<?php

declare(strict_types=1);

namespace MCF\Queue;

use Illuminate\Queue\QueueManager;
use MCF\Queue\McfQueueConnection;

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

        $this->manager->extend(
            'database',
            function ($app, array $config) {
                $connector = new \Illuminate\Queue\Connectors\DatabaseConnector(
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