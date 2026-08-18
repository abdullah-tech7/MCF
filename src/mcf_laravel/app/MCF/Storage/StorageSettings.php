<?php

namespace App\MCF\Storage;

final class StorageSettings
{
    /*
    |--------------------------------------------------------------------------
    | Default Storage
    |--------------------------------------------------------------------------
    |
    | The default storage provider and storage root used when
    | both values are not explicitly provided during an operation.
    |
    */

    public static string $defaultProvider = 'laravel';

    public static string $defaultStorageRoot = 'mcf';


    /*
    |--------------------------------------------------------------------------
    | Hosting / Service Limits
    |--------------------------------------------------------------------------
    |
    | These values represent the hosting environment limits known
    | to MCF Storage.
    |
    | These values do not modify PHP, Nginx, or Apache configuration.
    |
    | File size values are stored in bytes.
    |
    */


    /*
    | Maximum file size allowed by PHP during upload.
    |
    | 64 * 1024 * 1024 = 64 MB
    |
    */

    public static int $uploadMaxFileSize = 64 * 1024 * 1024;


    /*
    | Maximum size of the complete HTTP POST request allowed by PHP.
    |
    | 64 * 1024 * 1024 = 64 MB
    |
    */

    public static int $postMaxSize = 64 * 1024 * 1024;


    /*
    | Maximum HTTP request size allowed by the web server.
    |
    | This represents the configured limit of Nginx, Apache,
    | or another web server.
    |
    | 64 * 1024 * 1024 = 64 MB
    |
    */

    public static int $webServerRequestSize = 64 * 1024 * 1024;


    /*
    | Maximum allowed request processing time.
    |
    | Unit: seconds
    |
    */

    public static int $requestTimeoutSeconds = 30;


    /*
    |--------------------------------------------------------------------------
    | Shared Storage Limits
    |--------------------------------------------------------------------------
    |
    | These limits are controlled by MCF Storage and apply
    | independently of the selected storage provider.
    |
    */


    /*
    | Maximum number of files allowed in a single upload operation.
    |
    */

    public static int $maxFilesPerUpload = 5;


    /*
    | Maximum file size allowed by MCF Storage.
    |
    | 50 * 1024 * 1024 = 50 MB
    |
    */

    public static int $maxFileSize = 50 * 1024 * 1024;


    /*
    |--------------------------------------------------------------------------
    | Protected URL
    |--------------------------------------------------------------------------
    |
    | Default lifetime of temporary URLs generated for protected files.
    |
    | Unit: minutes
    |
    */

    public static int $temporaryUrlExpirationMinutes = 10;


    /*
    |--------------------------------------------------------------------------
    | Supported File Types
    |--------------------------------------------------------------------------
    |
    | The key represents the logical file type.
    |
    | The values represent the supported file extensions
    | for that type.
    |
    */

    public static array $supportedFileTypes = [
        'document' => [
            'pdf',
            'doc',
            'docx',
        ],

        'spreadsheet' => [
            'xls',
            'xlsx',
            'csv',
        ],

        'presentation' => [
            'ppt',
            'pptx',
        ],

        'image' => [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp',
            'svg',
        ],

        'text' => [
            'txt',
        ],
    ];


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */


    /*
    | Check whether the given file extension is supported.
    |
    */

    public static function isSupportedExtension(
        string $extension,
    ): bool {
        $extension = strtolower(
            ltrim(trim($extension), '.'),
        );

        foreach (self::$supportedFileTypes as $extensions) {
            if (in_array($extension, $extensions, true)) {
                return true;
            }
        }

        return false;
    }


    /*
    | Resolve the logical file type from the given extension.
    |
    | Returns null when the extension is not supported.
    |
    */

    public static function typeForExtension(
        string $extension,
    ): ?string {
        $extension = strtolower(
            ltrim(trim($extension), '.'),
        );

        foreach (self::$supportedFileTypes as $type => $extensions) {
            if (in_array($extension, $extensions, true)) {
                return $type;
            }
        }

        return null;
    }
}
