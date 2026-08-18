<?php

namespace App\MCF\Storage\Data;

final class StorageViewData
{
    public function __construct(
        public readonly string $access,
        public readonly string $source,
        public readonly ?string $expiresAt,
        public readonly string $mimeType,
        public readonly int $size,
    ) {
    }

    public function isPublic(): bool
    {
        return strtolower($this->access) === 'public';
    }

    public function isProtected(): bool
    {
        return !$this->isPublic();
    }

    public function isTemporary(): bool
    {
        return $this->expiresAt !== null;
    }
}
