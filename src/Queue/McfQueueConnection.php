<?php

declare(strict_types=1);

namespace MCF\Queue;

use DateInterval;
use DateTimeInterface;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Contracts\Queue\Queue;

final class McfQueueConnection implements Queue
{
    public function __construct(
        private readonly Queue $connection,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    */

    public function size(
        ?string $queue = null,
    ): int {
        return $this->connection->size($queue);
    }

    public function push(
        object|string $job,
        mixed $data = '',
        ?string $queue = null,
    ): mixed {
        $result = $this->connection->push(
            $job,
            $data,
            $queue,
        );

        McfQueueRuntime::wake();

        return $result;
    }

    public function pushOn(
        string $queue,
        object|string $job,
        mixed $data = '',
    ): mixed {
        $result = $this->connection->pushOn(
            $queue,
            $job,
            $data,
        );

        McfQueueRuntime::wake();

        return $result;
    }

    public function pushRaw(
        string $payload,
        ?string $queue = null,
        array $options = [],
    ): mixed {
        $result = $this->connection->pushRaw(
            $payload,
            $queue,
            $options,
        );

        McfQueueRuntime::wake();

        return $result;
    }

    public function later(
        DateTimeInterface|DateInterval|int $delay,
        object|string $job,
        mixed $data = '',
        ?string $queue = null,
    ): mixed {
        $result = $this->connection->later(
            $delay,
            $job,
            $data,
            $queue,
        );

        McfQueueRuntime::wake();

        return $result;
    }

    public function laterOn(
        string $queue,
        DateTimeInterface|DateInterval|int $delay,
        object|string $job,
        mixed $data = '',
    ): mixed {
        $result = $this->connection->laterOn(
            $queue,
            $job,
            $data,
        );

        McfQueueRuntime::wake();

        return $result;
    }

    public function bulk(
        array $jobs,
        mixed $data = '',
        ?string $queue = null,
    ): mixed {
        $result = $this->connection->bulk(
            $jobs,
            $data,
            $queue,
        );

        McfQueueRuntime::wake();

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | Worker
    |--------------------------------------------------------------------------
    */

    public function pop(
        ?string $queue = null,
    ): ?Job {
        return $this->connection->pop($queue);
    }

    /*
    |--------------------------------------------------------------------------
    | Connection
    |--------------------------------------------------------------------------
    */

    public function getConnectionName(): string
    {
        return $this->connection->getConnectionName();
    }

    public function setConnectionName(
        string $name,
    ): static {
        $this->connection->setConnectionName($name);

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Queue Statistics
    |--------------------------------------------------------------------------
    */

    public function pendingSize(
        ?string $queue = null,
    ): int {
        return $this->connection->pendingSize($queue);
    }

    public function delayedSize(
        ?string $queue = null,
    ): int {
        return $this->connection->delayedSize($queue);
    }

    public function reservedSize(
        ?string $queue = null,
    ): int {
        return $this->connection->reservedSize($queue);
    }

    public function creationTimeOfOldestPendingJob(
        ?string $queue = null,
    ): ?int {
        return $this->connection->creationTimeOfOldestPendingJob(
            $queue,
        );
    }
}