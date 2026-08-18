<?php

namespace App\MCF\Storage\Data;

final class McfStorageResult
{
    public function __construct(
        public readonly bool $success,
        public readonly StorageResultCode $code,
        public readonly string $message,
        public readonly mixed $data = null,
    ) {
    }

    public static function success(
        StorageResultCode $code,
        string $message,
        mixed $data = null,
    ): self {
        return new self(
            success: true,
            code: $code,
            message: $message,
            data: $data,
        );
    }

    public static function failure(
        StorageResultCode $code,
        string $message,
        mixed $data = null,
    ): self {
        return new self(
            success: false,
            code: $code,
            message: $message,
            data: $data,
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return !$this->success;
    }
}
