<?php

namespace App\MCF\Storage\Data;

final class StorageMetadata
{
    public function __construct(
        public readonly string $originalName,
        public readonly string $extension,
        public readonly string $type,
        public readonly string $mimeType,
        public readonly int $size,
    ) {
    }

    public function toArray(): array
    {
        return [
            'original_name' => $this->originalName,
            'extension' => $this->extension,
            'type' => $this->type,
            'mime_type' => $this->mimeType,
            'size' => $this->size,
        ];
    }
}
