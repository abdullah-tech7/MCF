<?php

namespace App\MCF\Storage\Data;

final class ProviderFileMetadata
{
    public function __construct(
        public readonly string $mimeType,
        public readonly int $size,
    ) {
    }
}
