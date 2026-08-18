<?php

namespace App\MCF\Storage\Providers\Laravel;

use App\MCF\Storage\Contracts\StorageProvider;
use App\MCF\Storage\Data\ProviderFileMetadata;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use RuntimeException;

final class LaravelStorageProvider implements StorageProvider
{
    /*
    |--------------------------------------------------------------------------
    | Upload
    |--------------------------------------------------------------------------
    */

    public function upload(
        mixed $file,
        string $storageRoot,
        string $folder,
        string $reference,
    ): bool {
        $disk = Storage::disk($storageRoot);

        return $disk->putFileAs(
            $folder === 'root' ? '' : $folder,
            $file,
            $reference,
        ) !== false;
    }


    /*
    |--------------------------------------------------------------------------
    | Public URL
    |--------------------------------------------------------------------------
    */

    public function publicUrl(
        string $storageRoot,
        string $folder,
        string $reference,
    ): string {
        $disk = Storage::disk($storageRoot);

        $path = $this->buildPath(
            $folder,
            $reference,
        );

        if (!$disk->exists($path)) {
            throw new RuntimeException(
                'The requested file does not exist in the storage provider.',
            );
        }

        return route(
            'mcf.storage.public',
            [
                'reference' => $reference,
            ],
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Temporary URL
    |--------------------------------------------------------------------------
    */

    public function temporaryUrl(
        string $storageRoot,
        string $folder,
        string $reference,
        int $expirationMinutes,
    ): string {
        $disk = Storage::disk($storageRoot);

        $path = $this->buildPath(
            $folder,
            $reference,
        );

        if (!$disk->exists($path)) {
            throw new RuntimeException(
                'The requested file does not exist in the storage provider.',
            );
        }

        if ($expirationMinutes <= 0) {
            throw new RuntimeException(
                'The temporary URL expiration must be greater than zero.',
            );
        }

        return URL::temporarySignedRoute(
            'mcf.storage.temporary',
            now()->addMinutes($expirationMinutes),
            [
                'reference' => $reference,
            ],
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Download
    |--------------------------------------------------------------------------
    */

    public function download(
        string $storageRoot,
        string $folder,
        string $reference,
        string $originalName,
    ): mixed {
        $disk = Storage::disk($storageRoot);

        $path = $this->buildPath(
            $folder,
            $reference,
        );

        if (!$disk->exists($path)) {
            throw new RuntimeException(
                'The requested file does not exist in the storage provider.',
            );
        }

        return $disk->download(
            $path,
            $originalName,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Read Stream
    |--------------------------------------------------------------------------
    */

    public function readStream(
        string $storageRoot,
        string $folder,
        string $reference,
    ) {
        $disk = Storage::disk($storageRoot);

        $path = $this->buildPath(
            $folder,
            $reference,
        );

        if (!$disk->exists($path)) {
            throw new RuntimeException(
                'The requested file does not exist in the storage provider.',
            );
        }

        $stream = $disk->readStream(
            $path,
        );

        if ($stream === false || $stream === null) {
            throw new RuntimeException(
                'The requested file could not be opened as a stream.',
            );
        }

        return $stream;
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        string $storageRoot,
        string $folder,
        string $reference,
    ): bool {
        $disk = Storage::disk($storageRoot);

        $path = $this->buildPath(
            $folder,
            $reference,
        );

        return $disk->delete($path);
    }


    /*
    |--------------------------------------------------------------------------
    | Exists
    |--------------------------------------------------------------------------
    */

    public function exists(
        string $storageRoot,
        string $folder,
        string $reference,
    ): bool {
        $disk = Storage::disk($storageRoot);

        $path = $this->buildPath(
            $folder,
            $reference,
        );

        return $disk->exists($path);
    }


    /*
    |--------------------------------------------------------------------------
    | Metadata
    |--------------------------------------------------------------------------
    */

    public function metadata(
        string $storageRoot,
        string $folder,
        string $reference,
    ): ProviderFileMetadata {
        $disk = Storage::disk($storageRoot);

        $path = $this->buildPath(
            $folder,
            $reference,
        );

        if (!$disk->exists($path)) {
            throw new RuntimeException(
                'The requested file does not exist in the storage provider.',
            );
        }

        $mimeType = $disk->mimeType($path);

        if ($mimeType === false || $mimeType === '') {
            throw new RuntimeException(
                'The file MIME type could not be determined.',
            );
        }

        return new ProviderFileMetadata(
            mimeType: $mimeType,
            size: $disk->size($path),
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Internal
    |--------------------------------------------------------------------------
    */

    private function buildPath(
        string $folder,
        string $reference,
    ): string {
        $folder = trim($folder, '/');
        $reference = trim($reference, '/');

        if (
            $folder === ''
            || strtolower($folder) === 'root'
        ) {
            return $reference;
        }

        return $folder . '/' . $reference;
    }
}
