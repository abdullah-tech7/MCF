<?php

namespace App\MCF\Storage;

use App\MCF\Storage\Contracts\StorageProvider;
use App\MCF\Storage\Data\McfFileData;
use App\MCF\Storage\Data\McfStorageMultiResult;
use App\MCF\Storage\Data\McfStorageResult;
use App\MCF\Storage\Data\StorageMetadata;
use App\MCF\Storage\Data\StorageReference;
use App\MCF\Storage\Data\StorageResultCode;
use App\MCF\Storage\Data\StorageViewData;
use App\MCF\Storage\Data\StorageZipData;
use App\MCF\Storage\Providers\Laravel\LaravelStorageProvider;
use App\MCF\Storage\Registry\StorageRegistry;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final class McfStorage
{
    private function __construct()
    {
    }


    /*
    |--------------------------------------------------------------------------
    | All
    |--------------------------------------------------------------------------
    */

    public static function all(): McfStorageResult
    {
        $registry = new StorageRegistry();

        try {
            if (! $registry->tableExists()) {
                return McfStorageResult::failure(
                    StorageResultCode::TABLE_NOT_FOUND,
                    'The MCF Storage table does not exist.',
                );
            }

            $records = $registry->all();

            return McfStorageResult::success(
                StorageResultCode::RECORDS_RETRIEVED,
                'The storage records were retrieved successfully.',
                $records,
            );
        } catch (Throwable $exception) {
            return McfStorageResult::failure(
                StorageResultCode::PROVIDER_ERROR,
                $exception->getMessage(),
            );
        }
    }

    public static function find(
    StorageReference|string $reference,
): McfStorageResult {
    $registry = new StorageRegistry();

    try {
        if (! $registry->tableExists()) {
            return McfStorageResult::failure(
                StorageResultCode::TABLE_NOT_FOUND,
                'The MCF Storage table does not exist.',
            );
        }

        $reference = self::resolveReference(
            $reference,
        );

        if ($reference === null) {
            return McfStorageResult::failure(
                StorageResultCode::INVALID_STORAGE,
                'The storage reference is invalid.',
            );
        }

        $record = $registry->find(
            $reference,
        );

        if ($record === null) {
            return McfStorageResult::failure(
                StorageResultCode::RECORD_NOT_FOUND,
                'The storage record does not exist.',
            );
        }

        return McfStorageResult::success(
            StorageResultCode::RECORD_RETRIEVED,
            'The storage record was retrieved successfully.',
            $record,
        );
    } catch (Throwable $exception) {
        return McfStorageResult::failure(
            StorageResultCode::PROVIDER_ERROR,
            $exception->getMessage(),
        );
    }
}


    /*
    |--------------------------------------------------------------------------
    | Upload
    |--------------------------------------------------------------------------
    */

    public static function upload(
        McfFileData $fileData,
    ): McfStorageResult {
        $registry = new StorageRegistry();

        $uploadedProvider = null;
        $uploadedStorageRoot = null;
        $uploadedFolder = null;
        $uploadedReference = null;

        try {
            if (! $registry->tableExists()) {
                return McfStorageResult::failure(
                    StorageResultCode::TABLE_NOT_FOUND,
                    'The MCF Storage table does not exist.',
                );
            }

            $file = self::resolveUploadedFile(
                $fileData->file,
            );

            if ($file === null) {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_FILE,
                    'The provided file is invalid.',
                );
            }

            $originalName = trim(
                $file->getClientOriginalName(),
            );

            if ($originalName === '') {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_FILE,
                    'The file does not have a valid original name.',
                );
            }

            $extension = strtolower(
                ltrim(
                    trim(
                        $file->getClientOriginalExtension(),
                    ),
                    '.',
                ),
            );

            if ($extension === '') {
                return McfStorageResult::failure(
                    StorageResultCode::UNSUPPORTED_FILE_TYPE,
                    'The file does not have a valid extension.',
                );
            }

            if (! StorageSettings::isSupportedExtension($extension)) {
                return McfStorageResult::failure(
                    StorageResultCode::UNSUPPORTED_FILE_TYPE,
                    'The file type is not supported by MCF Storage.',
                );
            }

            $size = (int) $file->getSize();

            if ($size <= 0) {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_FILE,
                    'The file size is invalid.',
                );
            }

            $maxFileSize = self::effectiveMaxFileSize();

            if ($size > $maxFileSize) {
                return McfStorageResult::failure(
                    StorageResultCode::FILE_TOO_LARGE,
                    'The file exceeds the maximum allowed size.',
                );
            }

            $mimeType = $file->getMimeType();

            if (
                $mimeType === false ||
                $mimeType === null ||
                $mimeType === ''
            ) {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_FILE,
                    'The file MIME type could not be determined.',
                );
            }

            $type = StorageSettings::typeForExtension(
                $extension,
            );

            if ($type === null) {
                return McfStorageResult::failure(
                    StorageResultCode::UNSUPPORTED_FILE_TYPE,
                    'The file type is not supported by MCF Storage.',
                );
            }

            $folder = self::normalizeFolder(
                $fileData->folder,
            );

            if ($folder === '') {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_FILE,
                    'The storage folder is invalid.',
                );
            }

            $access = self::normalizeAccess(
                $fileData->access,
            );

            [
                $providerName,
                $storageRoot,
            ] = self::resolveStorage(
                $fileData,
            );

            if (
                $providerName === '' ||
                $storageRoot === ''
            ) {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_STORAGE,
                    'The storage provider configuration is invalid.',
                );
            }

            $provider = self::resolveProvider(
                $providerName,
            );

            if ($provider === null) {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_STORAGE,
                    'The requested storage provider is not supported.',
                );
            }

            $reference = StorageReference::generate(
                $extension,
            );

            $uploadedProvider = $provider;
            $uploadedStorageRoot = $storageRoot;
            $uploadedFolder = $folder;
            $uploadedReference = $reference;

            $uploaded = $provider->upload(
                $file,
                $storageRoot,
                $folder,
                (string) $reference,
            );

            if (! $uploaded) {
                return McfStorageResult::failure(
                    StorageResultCode::UPLOAD_FAILED,
                    'The file could not be uploaded.',
                );
            }

            try {
                $record = $registry->create([
                    'reference' => (string) $reference,
                    'original_name' => $originalName,
                    'extension' => $extension,
                    'type' => $type,
                    'mime_type' => $mimeType,
                    'size' => $size,
                    'folder' => $folder,
                    'provider' => $providerName,
                    'storage_root' => $storageRoot,
                    'access' => $access,
                ]);
            } catch (Throwable $exception) {
                try {
                    $provider->delete(
                        $storageRoot,
                        $folder,
                        (string) $reference,
                    );
                } catch (Throwable) {
                    // Ignore cleanup failures.
                }

                throw $exception;
            }

            return McfStorageResult::success(
                StorageResultCode::UPLOADED,
                'The file was uploaded successfully.',
                $record->reference(),
            );
        } catch (Throwable $exception) {
            if (
                $uploadedProvider !== null &&
                $uploadedStorageRoot !== null &&
                $uploadedFolder !== null &&
                $uploadedReference !== null
            ) {
                try {
                    $uploadedProvider->delete(
                        $uploadedStorageRoot,
                        $uploadedFolder,
                        (string) $uploadedReference,
                    );
                } catch (Throwable) {
                    // Ignore cleanup failures.
                }
            }

            return McfStorageResult::failure(
                StorageResultCode::PROVIDER_ERROR,
                $exception->getMessage(),
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | View
    |--------------------------------------------------------------------------
    */

    public static function view(
        StorageReference|string $reference,
    ): McfStorageResult {
        $registry = new StorageRegistry();

        try {
            if (! $registry->tableExists()) {
                return McfStorageResult::failure(
                    StorageResultCode::TABLE_NOT_FOUND,
                    'The MCF Storage table does not exist.',
                );
            }

            $reference = self::resolveReference(
                $reference,
            );

            if ($reference === null) {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_STORAGE,
                    'The storage reference is invalid.',
                );
            }

            $record = $registry->find(
                $reference,
            );

            if ($record === null) {
                return McfStorageResult::failure(
                    StorageResultCode::RECORD_NOT_FOUND,
                    'The storage record does not exist.',
                );
            }

            $provider = self::resolveProvider(
                $record->provider,
            );

            if ($provider === null) {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_STORAGE,
                    'The storage provider is not supported.',
                );
            }

            $providerReference = (string) $record->reference();

            if (
                ! $provider->exists(
                    $record->storageRoot,
                    $record->folder,
                    $providerReference,
                )
            ) {
                return McfStorageResult::failure(
                    StorageResultCode::FILE_MISSING,
                    'The file does not exist in the storage provider.',
                );
            }

            $providerMetadata = $provider->metadata(
                $record->storageRoot,
                $record->folder,
                $providerReference,
            );

            if ($record->isPublic()) {
                $source = $provider->publicUrl(
                    $record->storageRoot,
                    $record->folder,
                    $providerReference,
                );

                $expiresAt = null;
            } else {
                $expirationMinutes =
                    StorageSettings::$temporaryUrlExpirationMinutes;

                $source = $provider->temporaryUrl(
                    $record->storageRoot,
                    $record->folder,
                    $providerReference,
                    $expirationMinutes,
                );

                $expiresAt = now()
                    ->addMinutes($expirationMinutes)
                    ->toDateTimeString();
            }

            $viewData = new StorageViewData(
                access: $record->access,
                source: $source,
                expiresAt: $expiresAt,
                mimeType: $providerMetadata->mimeType,
                size: $providerMetadata->size,
            );

            return McfStorageResult::success(
                StorageResultCode::VIEWED,
                'The file view source was generated successfully.',
                $viewData,
            );
        } catch (Throwable $exception) {
            return McfStorageResult::failure(
                StorageResultCode::VIEW_FAILED,
                $exception->getMessage(),
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Download
    |--------------------------------------------------------------------------
    */

    public static function download(
        StorageReference|string $reference,
    ): McfStorageResult {
        $registry = new StorageRegistry();

        try {
            if (! $registry->tableExists()) {
                return McfStorageResult::failure(
                    StorageResultCode::TABLE_NOT_FOUND,
                    'The MCF Storage table does not exist.',
                );
            }

            $reference = self::resolveReference(
                $reference,
            );

            if ($reference === null) {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_STORAGE,
                    'The storage reference is invalid.',
                );
            }

            $record = $registry->find(
                $reference,
            );

            if ($record === null) {
                return McfStorageResult::failure(
                    StorageResultCode::RECORD_NOT_FOUND,
                    'The storage record does not exist.',
                );
            }

            $provider = self::resolveProvider(
                $record->provider,
            );

            if ($provider === null) {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_STORAGE,
                    'The storage provider is not supported.',
                );
            }

            $providerReference = (string) $record->reference();

            if (
                ! $provider->exists(
                    $record->storageRoot,
                    $record->folder,
                    $providerReference,
                )
            ) {
                return McfStorageResult::failure(
                    StorageResultCode::FILE_MISSING,
                    'The file does not exist in the storage provider.',
                );
            }

            $response = $provider->download(
                $record->storageRoot,
                $record->folder,
                $providerReference,
                $record->originalName,
            );

            return McfStorageResult::success(
                StorageResultCode::DOWNLOADED,
                'The file download response was generated successfully.',
                $response,
            );
        } catch (Throwable $exception) {
            return McfStorageResult::failure(
                StorageResultCode::DOWNLOAD_FAILED,
                $exception->getMessage(),
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Download Many
    |--------------------------------------------------------------------------
    */

    public static function downloadMany(
        array $references,
    ): McfStorageResult {
        $registry = new StorageRegistry();

        $temporaryFiles = [];
        $zipPath = null;
        $zip = null;

        try {
            if (! $registry->tableExists()) {
                return McfStorageResult::failure(
                    StorageResultCode::TABLE_NOT_FOUND,
                    'The MCF Storage table does not exist.',
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Normalize References
            |--------------------------------------------------------------------------
            */

            $resolvedReferences = [];

            foreach ($references as $reference) {
                $resolvedReference = self::resolveReference(
                    $reference,
                );

                if ($resolvedReference === null) {
                    return McfStorageResult::failure(
                        StorageResultCode::INVALID_STORAGE,
                        'One of the storage references is invalid.',
                    );
                }

                $resolvedReferences[] = $resolvedReference;
            }

            $resolvedReferences = array_values(
                array_unique(
                    array_map(
                        fn (
                            StorageReference $reference,
                        ): string => (string) $reference,
                        $resolvedReferences,
                    ),
                ),
            );

            if ($resolvedReferences === []) {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_STORAGE,
                    'At least one storage reference is required.',
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Find All Records
            |--------------------------------------------------------------------------
            |
            | One database query only.
            |
            */

            $records = $registry->findMany(
                $resolvedReferences,
            );

            if (count($records) !== count($resolvedReferences)) {
                return McfStorageResult::failure(
                    StorageResultCode::RECORD_NOT_FOUND,
                    'One or more storage records do not exist.',
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Resolve Providers And Validate Files
            |--------------------------------------------------------------------------
            */

            $items = [];

            foreach ($records as $record) {
                $provider = self::resolveProvider(
                    $record->provider,
                );

                if ($provider === null) {
                    return McfStorageResult::failure(
                        StorageResultCode::INVALID_STORAGE,
                        'One of the storage providers is not supported.',
                    );
                }

                $providerReference = (string) $record->reference();

                if (
                    ! $provider->exists(
                        $record->storageRoot,
                        $record->folder,
                        $providerReference,
                    )
                ) {
                    return McfStorageResult::failure(
                        StorageResultCode::FILE_MISSING,
                        'One or more requested files do not exist in the storage provider.',
                    );
                }

                $items[] = [
                    'record' => $record,
                    'provider' => $provider,
                    'reference' => $providerReference,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | ZIP Extension
            |--------------------------------------------------------------------------
            */

            if (! class_exists(\ZipArchive::class)) {
                return McfStorageResult::failure(
                    StorageResultCode::ZIP_EXTENSION_MISSING,
                    'The PHP ZIP extension is required for multiple file downloads. Please enable the ZIP extension on your server.',
                );
            }

            /*
            |--------------------------------------------------------------------------
            | ZIP Name
            |--------------------------------------------------------------------------
            */

            $zipName = 'mcf-storage-'
                . now()->format('YmdHis')
                . now()->format('u')
                . '.zip';

            /*
            |--------------------------------------------------------------------------
            | Temporary ZIP
            |--------------------------------------------------------------------------
            */

            $zipPath = tempnam(
                sys_get_temp_dir(),
                'mcf-storage-',
            );

            if ($zipPath === false) {
                throw new RuntimeException(
                    'The temporary ZIP file could not be created.',
                );
            }

            $zip = new \ZipArchive();

            $openResult = $zip->open(
                $zipPath,
                \ZipArchive::CREATE | \ZipArchive::OVERWRITE,
            );

            if ($openResult !== true) {
                throw new RuntimeException(
                    'The ZIP archive could not be opened.',
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Add Files
            |--------------------------------------------------------------------------
            */

            $usedNames = [];

            foreach ($items as $item) {
                $record = $item['record'];
                $provider = $item['provider'];
                $providerReference = $item['reference'];

                $stream = $provider->readStream(
                    $record->storageRoot,
                    $record->folder,
                    $providerReference,
                );

                if (! is_resource($stream)) {
                    throw new RuntimeException(
                        'One of the requested files could not be opened.',
                    );
                }

                $temporaryFile = tempnam(
                    sys_get_temp_dir(),
                    'mcf-file-',
                );

                if ($temporaryFile === false) {
                    fclose($stream);

                    throw new RuntimeException(
                        'A temporary file could not be created.',
                    );
                }

                $temporaryFiles[] = $temporaryFile;

                $target = fopen(
                    $temporaryFile,
                    'wb',
                );

                if ($target === false) {
                    fclose($stream);

                    throw new RuntimeException(
                        'A temporary file could not be opened.',
                    );
                }

                stream_copy_to_stream(
                    $stream,
                    $target,
                );

                fclose($stream);
                fclose($target);

                $entryName = self::resolveZipEntryName(
                    $record->originalName,
                    $usedNames,
                );

                if (! $zip->addFile(
                    $temporaryFile,
                    $entryName,
                )) {
                    throw new RuntimeException(
                        'One of the files could not be added to the ZIP archive.',
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Finalize ZIP
            |--------------------------------------------------------------------------
            */

            if (! $zip->close()) {
                $zip = null;

                throw new RuntimeException(
                    'The ZIP archive could not be finalized.',
                );
            }

            $zip = null;

            return McfStorageResult::success(
                StorageResultCode::DOWNLOADED,
                'The files were prepared for download successfully.',
                new StorageZipData(
                    path: $zipPath,
                    name: $zipName,
                ),
            );
        } catch (Throwable $exception) {
            if ($zip instanceof \ZipArchive) {
                try {
                    $zip->close();
                } catch (Throwable) {
                    // Ignore cleanup failures.
                }
            }

            if ($zipPath !== null && is_file($zipPath)) {
                @unlink($zipPath);
            }

            return McfStorageResult::failure(
                StorageResultCode::DOWNLOAD_FAILED,
                $exception->getMessage(),
            );
        } finally {
            foreach ($temporaryFiles as $temporaryFile) {
                if (is_file($temporaryFile)) {
                    @unlink($temporaryFile);
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public static function delete(
        StorageReference|string $reference,
    ): McfStorageResult {
        $registry = new StorageRegistry();

        try {
            if (! $registry->tableExists()) {
                return McfStorageResult::failure(
                    StorageResultCode::TABLE_NOT_FOUND,
                    'The MCF Storage table does not exist.',
                );
            }

            $reference = self::resolveReference(
                $reference,
            );

            if ($reference === null) {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_STORAGE,
                    'The storage reference is invalid.',
                );
            }

            $record = $registry->find(
                $reference,
            );

            if ($record === null) {
                return McfStorageResult::failure(
                    StorageResultCode::RECORD_NOT_FOUND,
                    'The storage record does not exist.',
                );
            }

            $provider = self::resolveProvider(
                $record->provider,
            );

            if ($provider === null) {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_STORAGE,
                    'The storage provider is not supported.',
                );
            }

            $providerReference = (string) $record->reference();

            if (
                ! $provider->exists(
                    $record->storageRoot,
                    $record->folder,
                    $providerReference,
                )
            ) {
                return McfStorageResult::failure(
                    StorageResultCode::FILE_MISSING,
                    'The file does not exist in the storage provider.',
                );
            }

            $deleted = $provider->delete(
                $record->storageRoot,
                $record->folder,
                $providerReference,
            );

            if (! $deleted) {
                return McfStorageResult::failure(
                    StorageResultCode::DELETE_FAILED,
                    'The file could not be deleted from the storage provider.',
                );
            }

            if (! $registry->delete(
                $record->reference(),
            )) {
                return McfStorageResult::failure(
                    StorageResultCode::DELETE_FAILED,
                    'The file was deleted from storage, but its storage record could not be deleted.',
                );
            }

            return McfStorageResult::success(
                StorageResultCode::DELETED,
                'The file was deleted successfully.',
                true,
            );
        } catch (Throwable $exception) {
            return McfStorageResult::failure(
                StorageResultCode::DELETE_FAILED,
                $exception->getMessage(),
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Many
    |--------------------------------------------------------------------------
    */

    public static function deleteMany(
        array $references,
    ): McfStorageMultiResult {
        $registry = new StorageRegistry();

        $deletedReferences = [];

        try {
            if (! $registry->tableExists()) {
                return McfStorageMultiResult::failure(
                    StorageResultCode::TABLE_NOT_FOUND,
                    'The MCF Storage table does not exist.',
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Normalize References
            |--------------------------------------------------------------------------
            */

            $resolvedReferences = [];

            foreach ($references as $reference) {
                $resolvedReference = self::resolveReference(
                    $reference,
                );

                if ($resolvedReference === null) {
                    return McfStorageMultiResult::failure(
                        StorageResultCode::INVALID_STORAGE,
                        'One of the storage references is invalid.',
                    );
                }

                $resolvedReferences[] = $resolvedReference;
            }

            $resolvedReferences = array_values(
                array_unique(
                    array_map(
                        fn (
                            StorageReference $reference,
                        ): string => (string) $reference,
                        $resolvedReferences,
                    ),
                ),
            );

            if ($resolvedReferences === []) {
                return McfStorageMultiResult::failure(
                    StorageResultCode::INVALID_STORAGE,
                    'At least one storage reference is required.',
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Find All Records
            |--------------------------------------------------------------------------
            |
            | One database query only.
            |
            */

            $records = $registry->findMany(
                $resolvedReferences,
            );

            if (count($records) !== count($resolvedReferences)) {
                return McfStorageMultiResult::failure(
                    StorageResultCode::RECORD_NOT_FOUND,
                    'One or more storage records do not exist.',
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Validate Everything Before Deleting
            |--------------------------------------------------------------------------
            */

            $items = [];

            foreach ($records as $record) {
                $provider = self::resolveProvider(
                    $record->provider,
                );

                if ($provider === null) {
                    return McfStorageMultiResult::failure(
                        StorageResultCode::INVALID_STORAGE,
                        'One of the storage providers is not supported.',
                    );
                }

                $providerReference = (string) $record->reference();

                if (
                    ! $provider->exists(
                        $record->storageRoot,
                        $record->folder,
                        $providerReference,
                    )
                ) {
                    return McfStorageMultiResult::failure(
                        StorageResultCode::FILE_MISSING,
                        'One or more requested files do not exist in the storage provider.',
                    );
                }

                $items[] = [
                    'record' => $record,
                    'provider' => $provider,
                    'reference' => $providerReference,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Delete From Providers
            |--------------------------------------------------------------------------
            */

            foreach ($items as $item) {
                $deleted = $item['provider']->delete(
                    $item['record']->storageRoot,
                    $item['record']->folder,
                    $item['reference'],
                );

                if (! $deleted) {
                    return McfStorageMultiResult::failure(
                        StorageResultCode::DELETE_FAILED,
                        'One or more files could not be deleted from the storage provider.',
                    );
                }

                $deletedReferences[] =
                    $item['record']->reference();
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Registry Records
            |--------------------------------------------------------------------------
            */

            $registryDeleted = $registry->deleteMany(
                $deletedReferences,
            );

            if ($registryDeleted !== count($deletedReferences)) {
                return McfStorageMultiResult::failure(
                    StorageResultCode::DELETE_FAILED,
                    'The files were deleted from storage, but one or more storage records could not be deleted.',
                );
            }

            return McfStorageMultiResult::success(
                StorageResultCode::DELETED,
                'The selected files were deleted successfully.',
                $deletedReferences,
            );
        } catch (Throwable $exception) {
            return McfStorageMultiResult::failure(
                StorageResultCode::DELETE_FAILED,
                $exception->getMessage(),
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Exists
    |--------------------------------------------------------------------------
    */

    public static function exists(
        StorageReference|string $reference,
    ): McfStorageResult {
        $registry = new StorageRegistry();

        try {
            if (! $registry->tableExists()) {
                return McfStorageResult::failure(
                    StorageResultCode::TABLE_NOT_FOUND,
                    'The MCF Storage table does not exist.',
                );
            }

            $reference = self::resolveReference(
                $reference,
            );

            if ($reference === null) {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_STORAGE,
                    'The storage reference is invalid.',
                );
            }

            $record = $registry->find(
                $reference,
            );

            if ($record === null) {
                return McfStorageResult::success(
                    StorageResultCode::EXISTS,
                    'The storage record does not exist.',
                    false,
                );
            }

            $provider = self::resolveProvider(
                $record->provider,
            );

            if ($provider === null) {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_STORAGE,
                    'The storage provider is not supported.',
                );
            }

            $exists = $provider->exists(
                $record->storageRoot,
                $record->folder,
                (string) $record->reference(),
            );

            return McfStorageResult::success(
                StorageResultCode::EXISTS,
                'The storage existence check completed successfully.',
                $exists,
            );
        } catch (Throwable $exception) {
            return McfStorageResult::failure(
                StorageResultCode::PROVIDER_ERROR,
                $exception->getMessage(),
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Metadata
    |--------------------------------------------------------------------------
    */

    public static function metadata(
        StorageReference|string $reference,
    ): McfStorageResult {
        $registry = new StorageRegistry();

        try {
            if (! $registry->tableExists()) {
                return McfStorageResult::failure(
                    StorageResultCode::TABLE_NOT_FOUND,
                    'The MCF Storage table does not exist.',
                );
            }

            $reference = self::resolveReference(
                $reference,
            );

            if ($reference === null) {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_STORAGE,
                    'The storage reference is invalid.',
                );
            }

            $record = $registry->find(
                $reference,
            );

            if ($record === null) {
                return McfStorageResult::failure(
                    StorageResultCode::RECORD_NOT_FOUND,
                    'The storage record does not exist.',
                );
            }

            $provider = self::resolveProvider(
                $record->provider,
            );

            if ($provider === null) {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_STORAGE,
                    'The storage provider is not supported.',
                );
            }

            $providerReference = (string) $record->reference();

            if (
                ! $provider->exists(
                    $record->storageRoot,
                    $record->folder,
                    $providerReference,
                )
            ) {
                return McfStorageResult::failure(
                    StorageResultCode::FILE_MISSING,
                    'The file does not exist in the storage provider.',
                );
            }

            $providerMetadata = $provider->metadata(
                $record->storageRoot,
                $record->folder,
                $providerReference,
            );

            $metadata = new StorageMetadata(
                originalName: $record->originalName,
                extension: $record->extension,
                type: $record->type,
                mimeType: $providerMetadata->mimeType,
                size: $providerMetadata->size,
            );

            return McfStorageResult::success(
                StorageResultCode::METADATA_RETRIEVED,
                'The file metadata was retrieved successfully.',
                $metadata,
            );
        } catch (Throwable $exception) {
            return McfStorageResult::failure(
                StorageResultCode::METADATA_FAILED,
                $exception->getMessage(),
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Make Public
    |--------------------------------------------------------------------------
    */

    public static function makePublic(
        StorageReference|string $reference,
    ): McfStorageResult {
        return self::updateAccess(
            $reference,
            'public',
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Make Protected
    |--------------------------------------------------------------------------
    */

    public static function makeProtected(
        StorageReference|string $reference,
    ): McfStorageResult {
        return self::updateAccess(
            $reference,
            'protected',
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Upload Many
    |--------------------------------------------------------------------------
    */

    public static function uploadMany(
        array $fileDataList,
    ): McfStorageMultiResult {
        $registry = new StorageRegistry();

        $uploadedFiles = [];

        try {
            if (! $registry->tableExists()) {
                return McfStorageMultiResult::failure(
                    StorageResultCode::TABLE_NOT_FOUND,
                    'The MCF Storage table does not exist.',
                );
            }

            if ($fileDataList === []) {
                return McfStorageMultiResult::failure(
                    StorageResultCode::INVALID_FILE,
                    'At least one file is required.',
                );
            }

            foreach ($fileDataList as $fileData) {
                if (! $fileData instanceof McfFileData) {
                    return McfStorageMultiResult::failure(
                        StorageResultCode::INVALID_FILE,
                        'All storage upload items must be valid McfFileData instances.',
                    );
                }
            }

            return DB::transaction(
                function () use (
                    $fileDataList,
                    $registry,
                    &$uploadedFiles,
                ): McfStorageMultiResult {
                    $records = [];

                    foreach ($fileDataList as $fileData) {
                        $file = self::resolveUploadedFile(
                            $fileData->file,
                        );

                        if ($file === null) {
                            throw new RuntimeException(
                                'One of the provided files is invalid.',
                            );
                        }

                        $originalName = trim(
                            $file->getClientOriginalName(),
                        );

                        if ($originalName === '') {
                            throw new RuntimeException(
                                'One of the files does not have a valid original name.',
                            );
                        }

                        $extension = strtolower(
                            ltrim(
                                trim(
                                    $file->getClientOriginalExtension(),
                                ),
                                '.',
                            ),
                        );

                        if ($extension === '') {
                            throw new RuntimeException(
                                'One of the files does not have a valid extension.',
                            );
                        }

                        if (
                            ! StorageSettings::isSupportedExtension(
                                $extension,
                            )
                        ) {
                            throw new RuntimeException(
                                'One of the files has an unsupported file type.',
                            );
                        }

                        $size = (int) $file->getSize();

                        if ($size <= 0) {
                            throw new RuntimeException(
                                'One of the files has an invalid size.',
                            );
                        }

                        $maxFileSize = self::effectiveMaxFileSize();

                        if ($size > $maxFileSize) {
                            throw new RuntimeException(
                                'One of the files exceeds the maximum allowed size.',
                            );
                        }

                        $mimeType = $file->getMimeType();

                        if (
                            $mimeType === false ||
                            $mimeType === null ||
                            $mimeType === ''
                        ) {
                            throw new RuntimeException(
                                'The MIME type of one of the files could not be determined.',
                            );
                        }

                        $type = StorageSettings::typeForExtension(
                            $extension,
                        );

                        if ($type === null) {
                            throw new RuntimeException(
                                'One of the files has an unsupported file type.',
                            );
                        }

                        $folder = self::normalizeFolder(
                            $fileData->folder,
                        );

                        if ($folder === '') {
                            throw new RuntimeException(
                                'One of the storage folders is invalid.',
                            );
                        }

                        $access = self::normalizeAccess(
                            $fileData->access,
                        );

                        [
                            $providerName,
                            $storageRoot,
                        ] = self::resolveStorage(
                            $fileData,
                        );

                        if (
                            $providerName === '' ||
                            $storageRoot === ''
                        ) {
                            throw new RuntimeException(
                                'One of the storage provider configurations is invalid.',
                            );
                        }

                        $provider = self::resolveProvider(
                            $providerName,
                        );

                        if ($provider === null) {
                            throw new RuntimeException(
                                'One of the requested storage providers is not supported.',
                            );
                        }

                        $reference = StorageReference::generate(
                            $extension,
                        );

                        $uploaded = $provider->upload(
                            $file,
                            $storageRoot,
                            $folder,
                            (string) $reference,
                        );

                        if (! $uploaded) {
                            throw new RuntimeException(
                                'One of the files could not be uploaded.',
                            );
                        }

                        $uploadedFiles[] = [
                            'provider' => $provider,
                            'storageRoot' => $storageRoot,
                            'folder' => $folder,
                            'reference' => (string) $reference,
                        ];

                        $records[] = $registry->create([
                            'reference' => (string) $reference,
                            'original_name' => $originalName,
                            'extension' => $extension,
                            'type' => $type,
                            'mime_type' => $mimeType,
                            'size' => $size,
                            'folder' => $folder,
                            'provider' => $providerName,
                            'storage_root' => $storageRoot,
                            'access' => $access,
                        ]);
                    }

                    return McfStorageMultiResult::success(
                        StorageResultCode::UPLOADED,
                        'All files were uploaded successfully.',
                        $records,
                    );
                },
            );
        } catch (Throwable $exception) {
            foreach (
                array_reverse($uploadedFiles)
                as $uploadedFile
            ) {
                try {
                    $uploadedFile['provider']->delete(
                        $uploadedFile['storageRoot'],
                        $uploadedFile['folder'],
                        $uploadedFile['reference'],
                    );
                } catch (Throwable) {
                    // Ignore cleanup failures.
                }
            }

            return McfStorageMultiResult::failure(
                StorageResultCode::UPLOAD_FAILED,
                $exception->getMessage(),
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Internal
    |--------------------------------------------------------------------------
    */

    private static function updateAccess(
        StorageReference|string $reference,
        string $access,
    ): McfStorageResult {
        $registry = new StorageRegistry();

        try {
            if (! $registry->tableExists()) {
                return McfStorageResult::failure(
                    StorageResultCode::TABLE_NOT_FOUND,
                    'The MCF Storage table does not exist.',
                );
            }

            $reference = self::resolveReference(
                $reference,
            );

            if ($reference === null) {
                return McfStorageResult::failure(
                    StorageResultCode::INVALID_STORAGE,
                    'The storage reference is invalid.',
                );
            }

            $record = $registry->find(
                $reference,
            );

            if ($record === null) {
                return McfStorageResult::failure(
                    StorageResultCode::RECORD_NOT_FOUND,
                    'The storage record does not exist.',
                );
            }

            $updated = $registry->update(
                $record->reference(),
                [
                    'access' => $access,
                ],
            );

            if ($updated === null) {
                return McfStorageResult::failure(
                    StorageResultCode::RECORD_NOT_FOUND,
                    'The storage record does not exist.',
                );
            }

            return McfStorageResult::success(
                StorageResultCode::ACCESS_UPDATED,
                'The file access policy was updated successfully.',
                $updated,
            );
        } catch (Throwable $exception) {
            return McfStorageResult::failure(
                StorageResultCode::PROVIDER_ERROR,
                $exception->getMessage(),
            );
        }
    }


    private static function resolveReference(
        StorageReference|string $reference,
    ): ?StorageReference {
        if ($reference instanceof StorageReference) {
            return $reference;
        }

        if (! StorageReference::isValid($reference)) {
            return null;
        }

        return StorageReference::fromString(
            $reference,
        );
    }


    private static function resolveUploadedFile(
        mixed $file,
    ): mixed {
        if (
            ! is_object($file) ||
            ! method_exists($file, 'isValid') ||
            ! method_exists($file, 'getClientOriginalName') ||
            ! method_exists($file, 'getClientOriginalExtension') ||
            ! method_exists($file, 'getMimeType') ||
            ! method_exists($file, 'getSize')
        ) {
            return null;
        }

        if (! $file->isValid()) {
            return null;
        }

        return $file;
    }


    private static function resolveStorage(
        McfFileData $fileData,
    ): array {
        if ($fileData->hasExplicitStorage()) {
            return [
                strtolower(
                    trim($fileData->provider),
                ),
                trim($fileData->storageRoot),
            ];
        }

        return [
            strtolower(
                trim(StorageSettings::$defaultProvider),
            ),
            trim(StorageSettings::$defaultStorageRoot),
        ];
    }


    private static function resolveProvider(
        string $provider,
    ): ?StorageProvider {
        return match (strtolower(trim($provider))) {
            'laravel' => new LaravelStorageProvider(),
            default => null,
        };
    }


    private static function normalizeFolder(
        string $folder,
    ): string {
        $folder = strtolower(
            trim($folder),
        );

        $folder = trim(
            $folder,
            '/',
        );

        if (
            $folder === '' ||
            $folder === 'root'
        ) {
            return 'root';
        }

        return $folder;
    }


    private static function normalizeAccess(
        string $access,
    ): string {
        return strtolower(trim($access)) === 'public'
            ? 'public'
            : 'protected';
    }


    private static function effectiveMaxFileSize(): int
    {
        return min(
            StorageSettings::$maxFileSize,
            StorageSettings::$uploadMaxFileSize,
            StorageSettings::$postMaxSize,
            StorageSettings::$webServerRequestSize,
        );
    }


    private static function resolveZipEntryName(
        string $originalName,
        array &$usedNames,
    ): string {
        $originalName = trim(
            $originalName,
        );

        if ($originalName === '') {
            $originalName = 'file';
        }

        if (! isset($usedNames[$originalName])) {
            $usedNames[$originalName] = 1;

            return $originalName;
        }

        $extension = pathinfo(
            $originalName,
            PATHINFO_EXTENSION,
        );

        $baseName = pathinfo(
            $originalName,
            PATHINFO_FILENAME,
        );

        $counter = ++$usedNames[$originalName];

        do {
            $name = $extension !== ''
                ? $baseName . ' (' . $counter . ').' . $extension
                : $baseName . ' (' . $counter . ')';

            $counter++;
        } while (isset($usedNames[$name]));

        $usedNames[$originalName] = $counter - 1;
        $usedNames[$name] = 1;

        return $name;
    }
}
