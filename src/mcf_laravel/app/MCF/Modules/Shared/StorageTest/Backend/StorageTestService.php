<?php

namespace App\MCF\Modules\Shared\StorageTest\Backend;

use App\MCF\Base\MfcService;
use App\MCF\Storage\Data\McfFileData;
use App\MCF\Storage\Data\McfStorageMultiResult;
use App\MCF\Storage\Data\McfStorageResult;
use App\MCF\Storage\Data\StorageReference;
use App\MCF\Storage\McfStorage;

final class StorageTestService extends MfcService
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(): McfStorageResult
    {
        return McfStorage::all();
    }


    /*
    |--------------------------------------------------------------------------
    | Find
    |--------------------------------------------------------------------------
    */

    public function find(
        StorageReference|string $reference,
    ): McfStorageResult {
        return McfStorage::find(
            $reference,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Upload
    |--------------------------------------------------------------------------
    */

    public function upload(
        McfFileData $data,
    ): McfStorageResult {
        return McfStorage::upload(
            $data,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Upload Many
    |--------------------------------------------------------------------------
    */

    public function uploadMany(
        array $fileDataList,
    ): McfStorageMultiResult {
        return McfStorage::uploadMany(
            $fileDataList,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | View
    |--------------------------------------------------------------------------
    */

    public function view(
        StorageReference|string $reference,
    ): McfStorageResult {
        return McfStorage::view(
            $reference,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Download
    |--------------------------------------------------------------------------
    */

    public function download(
        StorageReference|string $reference,
    ): McfStorageResult {
        return McfStorage::download(
            $reference,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Download Many
    |--------------------------------------------------------------------------
    */

    public function downloadMany(
        array $references,
    ): McfStorageResult {
        return McfStorage::downloadMany(
            $references,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Metadata
    |--------------------------------------------------------------------------
    */

    public function metadata(
        StorageReference|string $reference,
    ): McfStorageResult {
        return McfStorage::metadata(
            $reference,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        StorageReference|string $reference,
    ): McfStorageResult {
        return McfStorage::delete(
            $reference,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Many
    |--------------------------------------------------------------------------
    */

    public function deleteMany(
        array $references,
    ): McfStorageMultiResult {
        return McfStorage::deleteMany(
            $references,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Make Public
    |--------------------------------------------------------------------------
    */

    public function makePublic(
        StorageReference|string $reference,
    ): McfStorageResult {
        return McfStorage::makePublic(
            $reference,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Make Protected
    |--------------------------------------------------------------------------
    */

    public function makeProtected(
        StorageReference|string $reference,
    ): McfStorageResult {
        return McfStorage::makeProtected(
            $reference,
        );
    }
}
