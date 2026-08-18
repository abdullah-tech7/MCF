<?php

namespace App\MCF\Storage\Data;

final class StorageRecord
{
    public function __construct(
        public readonly int $id,
        public readonly StorageReference $reference,
        public readonly string $originalName,
        public readonly string $extension,
        public readonly string $type,
        public readonly string $mimeType,
        public readonly int $size,
        public readonly string $folder,
        public readonly string $provider,
        public readonly string $storageRoot,
        public readonly string $access,
        public readonly mixed $createdAt = null,
        public readonly mixed $updatedAt = null,
    ) {
    }

    public function reference(): StorageReference
    {
        return $this->reference;
    }

    public function metadata(): StorageMetadata
    {
        return new StorageMetadata(
            originalName: $this->originalName,
            extension: $this->extension,
            type: $this->type,
            mimeType: $this->mimeType,
            size: $this->size,
        );
    }

    public function isPublic(): bool
    {
        return strtolower($this->access) === 'public';
    }

    public function isProtected(): bool
    {
        return !$this->isPublic();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'reference' => (string) $this->reference,
            'original_name' => $this->originalName,
            'extension' => $this->extension,
            'type' => $this->type,
            'mime_type' => $this->mimeType,
            'size' => $this->size,
            'folder' => $this->folder,
            'provider' => $this->provider,
            'storage_root' => $this->storageRoot,
            'access' => $this->access,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
