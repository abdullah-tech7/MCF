<?php
namespace App\MCF\Storage\Data;

enum StorageResultCode: string {
    /*
    |--------------------------------------------------------------------------
    | Operation Results
    |--------------------------------------------------------------------------
    */

    case UPLOADED = 'uploaded';

    case VIEWED = 'viewed';

    case DOWNLOADED = 'downloaded';

    case DELETED = 'deleted';

    case METADATA_RETRIEVED = 'metadata_retrieved';

    case RECORDS_RETRIEVED = 'records_retrieved';

    case EXISTS = 'exists';

    case ACCESS_UPDATED = 'access_updated';

    case RECORD_RETRIEVED = 'record_retrieved';

    /*
    |--------------------------------------------------------------------------
    | Storage Errors
    |--------------------------------------------------------------------------
    */

    case TABLE_NOT_FOUND = 'table_not_found';

    case RECORD_NOT_FOUND = 'record_not_found';

    case FILE_MISSING = 'file_missing';

    case FILE_TOO_LARGE = 'file_too_large';

    case TOO_MANY_FILES = 'too_many_files';

    case UNSUPPORTED_FILE_TYPE = 'unsupported_file_type';

    case INVALID_FILE = 'invalid_file';

    case UPLOAD_FAILED = 'upload_failed';

    case DOWNLOAD_FAILED = 'download_failed';

    case VIEW_FAILED = 'view_failed';

    case DELETE_FAILED = 'delete_failed';

    case METADATA_FAILED = 'metadata_failed';

    case PROVIDER_ERROR = 'provider_error';

    case INVALID_STORAGE = 'invalid_storage';

    case REQUEST_TIMEOUT = 'request_timeout';

    case ZIP_EXTENSION_MISSING = 'zip_extension_missing';

}
