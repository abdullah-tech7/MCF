<?php

namespace App\MCF\Storage\Contracts;

use App\MCF\Storage\Data\ProviderFileMetadata;

interface StorageProvider
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
    ): bool;


    /*
    |--------------------------------------------------------------------------
    | Public URL
    |--------------------------------------------------------------------------
    */

    public function publicUrl(
        string $storageRoot,
        string $folder,
        string $reference,
    ): string;


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
    ): string;


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
    ): mixed;


    /*
    |--------------------------------------------------------------------------
    | Read Stream
    |--------------------------------------------------------------------------
    */

    public function readStream(
        string $storageRoot,
        string $folder,
        string $reference,
    );


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        string $storageRoot,
        string $folder,
        string $reference,
    ): bool;


    /*
    |--------------------------------------------------------------------------
    | Exists
    |--------------------------------------------------------------------------
    */

    public function exists(
        string $storageRoot,
        string $folder,
        string $reference,
    ): bool;


    /*
    |--------------------------------------------------------------------------
    | Metadata
    |--------------------------------------------------------------------------
    */

    public function metadata(
        string $storageRoot,
        string $folder,
        string $reference,
    ): ProviderFileMetadata;
}
