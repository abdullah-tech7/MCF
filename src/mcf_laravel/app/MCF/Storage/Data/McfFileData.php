<?php

namespace App\MCF\Storage\Data;

use InvalidArgumentException;

final class McfFileData
{
    public function __construct(
        public readonly mixed $file,
        public readonly string $folder,
        public readonly string $access,
        public readonly ?string $provider = null,
        public readonly ?string $storageRoot = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->file === null) {
            throw new InvalidArgumentException(
                'A file is required.'
            );
        }

        if (trim($this->folder) === '') {
            throw new InvalidArgumentException(
                'A storage folder is required.'
            );
        }

        if (trim($this->access) === '') {
            throw new InvalidArgumentException(
                'A storage access value is required.'
            );
        }

        $hasProvider = $this->provider !== null
            && trim($this->provider) !== '';

        $hasStorageRoot = $this->storageRoot !== null
            && trim($this->storageRoot) !== '';

        if ($hasProvider !== $hasStorageRoot) {
            throw new InvalidArgumentException(
                'Provider and storage root must be provided together.'
            );
        }
    }

    public function hasExplicitStorage(): bool
    {
        return $this->provider !== null
            && trim($this->provider) !== ''
            && $this->storageRoot !== null
            && trim($this->storageRoot) !== '';
    }
}
