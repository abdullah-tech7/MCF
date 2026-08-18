<?php

namespace App\MCF\Storage\Data;

final class StorageZipData
{
    public function __construct(
        public readonly string $path,
        public readonly string $name,
    ) {
    }
}
