# MCF Storage

> A provider-independent file storage abstraction for the MCF framework.

## Overview

MCF Storage provides a unified API for uploading, finding, viewing,
downloading, deleting, and managing files without coupling application
modules to a concrete storage backend.

Core concepts:

-   `McfStorage` --- public storage API.
-   `StorageReference` --- internal identity of a stored file.
-   `StorageRecord` --- MCF registry record.
-   `McfFileData` --- upload input.
-   `McfStorageResult` --- result of a single operation.
-   `McfStorageMultiResult` --- result of a bulk operation.
-   `StorageRegistry` --- persistence of MCF storage records.
-   `StorageProvider` --- abstraction over the physical storage backend.

The application should normally depend on `McfStorage`, not on Laravel's
filesystem or another concrete provider.

## Architecture

``` text
Application
    |
    v
McfStorage
    |
    +--------------------+
    |                    |
    v                    v
StorageRegistry      StorageProvider
    |                    |
    v                    v
mcf_storage          Physical Storage
database             Laravel / S3 / Custom
```

### StorageRegistry

The registry stores MCF information about a file:

-   reference
-   original name
-   extension
-   type
-   MIME type
-   size
-   folder
-   provider
-   storage root
-   access
-   timestamps

It does not store the physical file.

### StorageProvider

A provider performs the physical storage operations.

``` php
interface StorageProvider
{
    public function upload(
        mixed $file,
        string $storageRoot,
        string $folder,
        string $reference,
    ): bool;

    public function publicUrl(
        string $storageRoot,
        string $folder,
        string $reference,
    ): string;

    public function temporaryUrl(
        string $storageRoot,
        string $folder,
        string $reference,
        int $expirationMinutes,
    ): string;

    public function download(
        string $storageRoot,
        string $folder,
        string $reference,
        string $originalName,
    ): mixed;

    public function delete(
        string $storageRoot,
        string $folder,
        string $reference,
    ): bool;

    public function exists(
        string $storageRoot,
        string $folder,
        string $reference,
    ): bool;

    public function metadata(
        string $storageRoot,
        string $folder,
        string $reference,
    ): ProviderFileMetadata;
}
```

A provider may internally use Laravel Filesystem, S3, an SDK, or another
service. That implementation detail is hidden from application modules.

------------------------------------------------------------------------

# StorageReference

A `StorageReference` is the identity of a stored file.

``` php
$reference = StorageReference::generate(
    $extension,
);
```

The reference should be used as the storage identity instead of the
original filename.

Example:

``` text
Original name:
invoice.pdf

Storage reference:
20260818023227059301.pdf
```

The original filename remains metadata and is used for user-facing
downloads.

------------------------------------------------------------------------

# StorageRecord

A `StorageRecord` represents the MCF registry information for one file.

``` php
$result = McfStorage::find($reference);

if ($result->isSuccess()) {
    $record = $result->data;

    $record->reference();
    $record->originalName;
    $record->extension;
    $record->type;
    $record->mimeType;
    $record->size;
    $record->folder;
    $record->provider;
    $record->storageRoot;
    $record->access;
}
```

`find()` is for the MCF registry record.

`metadata()` is for provider-level file metadata.

------------------------------------------------------------------------

# Registry Data vs Provider Metadata

## Find

``` php
$result = McfStorage::find($reference);
```

Use it for MCF information such as:

-   provider
-   storage root
-   folder
-   access
-   original filename
-   reference

## Metadata

``` php
$result = McfStorage::metadata($reference);
```

Use it for provider-derived information such as:

-   MIME type
-   physical size
-   provider metadata

------------------------------------------------------------------------

# Upload

## Single Upload

``` php
$data = new McfFileData(
    file: $file,
    folder: 'documents',
    access: 'protected',
    provider: null,
    storageRoot: null,
);

$result = McfStorage::upload($data);

if ($result->isSuccess()) {
    $reference = $result->data;
}
```

MCF generates the `StorageReference`.

## Multi Upload

``` php
$result = McfStorage::uploadMany([
    new McfFileData(
        file: $file1,
        folder: 'documents',
        access: 'protected',
        provider: null,
        storageRoot: null,
    ),
    new McfFileData(
        file: $file2,
        folder: 'documents',
        access: 'protected',
        provider: null,
        storageRoot: null,
    ),
]);
```

Multi-upload is treated as one workflow. If validation or upload fails,
previously uploaded provider files are cleaned up where possible.

------------------------------------------------------------------------

# View

``` php
$result = McfStorage::view($reference);

if ($result->isSuccess()) {
    $view = $result->data;

    $view->source;
    $view->access;
    $view->expiresAt;
    $view->mimeType;
    $view->size;
}
```

## Public

A public file receives a permanent public source:

``` text
public
  -> publicUrl()
  -> permanent source
```

## Protected

A protected file receives a temporary source:

``` text
protected
  -> temporaryUrl()
  -> temporary signed source
  -> expires
```

Protected means the generated viewing source has a limited lifetime. It
is not an application authorization system.

Authorization belongs to the application's access-control layer.

------------------------------------------------------------------------

# Download

## Single

``` php
$result = McfStorage::download($reference);

if ($result->isSuccess()) {
    return $result->data;
}
```

The provider receives the original filename:

``` php
$provider->download(
    $storageRoot,
    $folder,
    $reference,
    $originalName,
);
```

Therefore the user receives the original filename instead of the
internal reference.

## Multi

``` php
$result = McfStorage::downloadMany([
    $reference1,
    $reference2,
    $reference3,
]);
```

The bulk workflow:

1.  Normalizes references.
2.  Removes duplicates.
3.  Uses `StorageRegistry::findMany()` to retrieve records in bulk.
4.  Validates records and providers.
5.  Reads the physical files.
6.  Creates a ZIP archive.
7.  Uses original filenames inside the archive.
8.  Resolves duplicate original filenames.

Example:

``` text
invoice.pdf
invoice.pdf
report.xlsx
```

can become:

``` text
invoice.pdf
invoice (2).pdf
report.xlsx
```

A timestamped archive name can be used, for example:

``` text
mcf-storage-20260818023227059301.zip
```

------------------------------------------------------------------------

# Single vs Multi

Recommended UI behavior:

``` text
0 selected -> do nothing
1 selected -> single operation
2+ selected -> multi operation
```

For downloads:

``` text
1 -> McfStorage::download()
2+ -> McfStorage::downloadMany()
```

For deletion:

``` text
1 -> McfStorage::delete()
2+ -> McfStorage::deleteMany()
```

------------------------------------------------------------------------

# Delete

## Single

``` php
$result = McfStorage::delete($reference);
```

The operation removes the physical file through the provider and then
removes the MCF registry record.

## Multi

``` php
$result = McfStorage::deleteMany([
    $reference1,
    $reference2,
    $reference3,
]);
```

The workflow first retrieves all records with one bulk lookup:

``` text
references[]
    -> StorageRegistry::findMany()
    -> StorageRecord[]
```

It validates providers and files, deletes the physical files, and
removes the corresponding registry records.

------------------------------------------------------------------------

# Bulk Registry Lookup

For bulk operations, use:

``` php
$records = $registry->findMany($references);
```

instead of:

``` php
$registry->find($reference1);
$registry->find($reference2);
$registry->find($reference3);
```

The bulk lookup is designed to use one database query based on:

``` sql
WHERE reference IN (...)
```

This is important for multi-download and multi-delete performance.

------------------------------------------------------------------------

# Exists

``` php
$result = McfStorage::exists($reference);

if ($result->isSuccess()) {
    $exists = $result->data;
}
```

------------------------------------------------------------------------

# Public and Protected

``` php
McfStorage::makePublic($reference);

McfStorage::makeProtected($reference);
```

These operations change the storage access policy stored in the MCF
registry.

They do not replace application authorization.

------------------------------------------------------------------------

# Multiple Providers

Multiple providers can coexist:

``` text
                    McfStorage
                        |
                StorageProvider
                  /     |                       v      v       v
             Laravel    S3    Custom
```

A record identifies its backend using values such as:

``` text
provider
storage_root
folder
reference
```

Example:

``` text
provider: laravel
storage_root: public
folder: documents
```

Another record can use:

``` text
provider: s3
storage_root: private
folder: documents
```

Application code remains:

``` php
McfStorage::upload($data);
```

The provider is resolved internally.

------------------------------------------------------------------------

# Adding a Provider

Implement `StorageProvider`:

``` php
final class S3StorageProvider implements StorageProvider
{
    public function upload(
        mixed $file,
        string $storageRoot,
        string $folder,
        string $reference,
    ): bool {
        // S3 upload
    }

    public function publicUrl(
        string $storageRoot,
        string $folder,
        string $reference,
    ): string {
        // Public URL
    }

    public function temporaryUrl(
        string $storageRoot,
        string $folder,
        string $reference,
        int $expirationMinutes,
    ): string {
        // Temporary signed URL
    }

    public function download(
        string $storageRoot,
        string $folder,
        string $reference,
        string $originalName,
    ): mixed {
        // Download
    }

    public function delete(
        string $storageRoot,
        string $folder,
        string $reference,
    ): bool {
        // Delete
    }

    public function exists(
        string $storageRoot,
        string $folder,
        string $reference,
    ): bool {
        // Exists
    }

    public function metadata(
        string $storageRoot,
        string $folder,
        string $reference,
    ): ProviderFileMetadata {
        // Metadata
    }
}
```

Register the provider in the provider resolver used by `McfStorage`.

No application-level storage API needs to change.

------------------------------------------------------------------------

# Provider Responsibilities

A provider is responsible for:

-   physical storage
-   upload
-   reading
-   deletion
-   existence checks
-   public URLs
-   temporary URLs
-   provider metadata

A provider is not responsible for:

-   application authorization
-   business rules
-   ownership
-   UI
-   application workflows
-   generating the MCF reference

------------------------------------------------------------------------

# Registry Responsibilities

The registry is responsible for:

-   storage references
-   original names
-   file metadata
-   provider identification
-   storage root
-   folder
-   access policy
-   timestamps
-   finding records
-   bulk lookup
-   registry deletion

It does not replace the provider.

------------------------------------------------------------------------

# Result Handling

Single operations return:

``` php
McfStorageResult
```

Bulk operations return:

``` php
McfStorageMultiResult
```

Example:

``` php
$result = McfStorage::download($reference);

if ($result->isFailure()) {
    // Handle failure.
    return;
}

$data = $result->data;
```

A result contains:

``` php
$result->success;
$result->code;
$result->message;
$result->data;
```

Use `code` for programmatic decisions and `message` for human-readable
feedback.

------------------------------------------------------------------------

# Common Result Codes

``` text
uploaded
viewed
downloaded
deleted
metadata_retrieved
record_retrieved
records_retrieved
exists
access_updated

table_not_found
record_not_found
file_missing
file_too_large
too_many_files
unsupported_file_type
invalid_file
upload_failed
download_failed
view_failed
delete_failed
metadata_failed
provider_error
invalid_storage
request_timeout
zip_extension_missing
```

------------------------------------------------------------------------

# Application Example

``` php
public function store(Request $request)
{
    $data = new McfFileData(
        file: $request->file('file'),
        folder: 'documents',
        access: 'protected',
    );

    $result = McfStorage::upload($data);

    if ($result->isFailure()) {
        return back()->with(
            'error',
            $result->message,
        );
    }

    $reference = $result->data;

    // Store the reference in application business data.
}
```

## View Example

``` php
$result = McfStorage::view($reference);

if ($result->isFailure()) {
    abort(404);
}

$source = $result->data->source;
```

## Download Example

``` php
$result = McfStorage::download($reference);

if ($result->isFailure()) {
    abort(404);
}

return $result->data;
```

## Delete Example

``` php
$result = McfStorage::delete($reference);

if ($result->isFailure()) {
    // Handle failure.
}
```

------------------------------------------------------------------------

# Recommended Integration Pattern

``` text
Controller
    |
    v
Application Service
    |
    v
McfStorage
    |
    +--> StorageRegistry
    |
    +--> StorageProvider
```

Avoid coupling application modules directly to a concrete provider:

``` text
Controller
    |
    v
Laravel Storage
```

when the file is managed by MCF Storage.

------------------------------------------------------------------------

# Summary

The core separation is:

``` text
McfStorage
    = public storage API

StorageRegistry
    = MCF storage records

StorageProvider
    = physical storage implementation

StorageReference
    = storage identity

Original Name
    = user-facing filename
```

The main public API is:

``` php
McfStorage::all()
McfStorage::upload()
McfStorage::uploadMany()
McfStorage::find()
McfStorage::view()
McfStorage::download()
McfStorage::downloadMany()
McfStorage::metadata()
McfStorage::exists()
McfStorage::delete()
McfStorage::deleteMany()
McfStorage::makePublic()
McfStorage::makeProtected()
```

The result is a provider-independent storage layer that can support
multiple storage backends without changing application-facing storage
calls.
