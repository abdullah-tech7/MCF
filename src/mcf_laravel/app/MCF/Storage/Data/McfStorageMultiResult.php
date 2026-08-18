<?php

namespace App\MCF\Storage\Data;

final class McfStorageMultiResult
{
    public function __construct(
        public readonly bool $success,
        public readonly StorageResultCode $code,
        public readonly string $message,
        public readonly array $data = [],
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    public static function success(
        StorageResultCode $code,
        string $message,
        array $data = [],
    ): self {
        return new self(
            success: true,
            code: $code,
            message: $message,
            data: $data,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Failure
    |--------------------------------------------------------------------------
    */

    public static function failure(
        StorageResultCode $code,
        string $message,
    ): self {
        return new self(
            success: false,
            code: $code,
            message: $message,
            data: [],
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return !$this->success;
    }
}
