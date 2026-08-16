<?php

declare(strict_types=1);

namespace App\MCF\Result;

abstract class McfResult
{
    public function __construct(
        protected readonly string $result,
    ) {
    }

    public function result(): string
    {
        return $this->result;
    }

    public function is(string $result): bool
    {
        return $this->result === $result;
    }
}
