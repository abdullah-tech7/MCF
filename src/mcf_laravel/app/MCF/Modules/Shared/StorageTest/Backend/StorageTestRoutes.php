<?php

use Illuminate\Support\Facades\Route;
use App\MCF\AccessControl\Registry\McfRouteDataRegistry;
use App\MCF\Modules\Shared\StorageTest\Backend\StorageTestController;


/*
|--------------------------------------------------------------------------
| Storage Test
|--------------------------------------------------------------------------
|
| Test workflow for MCF Storage.
|
*/


/*
|--------------------------------------------------------------------------
| Index
|--------------------------------------------------------------------------
*/

Route::get(
    '/storageTest',
    [StorageTestController::class, 'index'],
)->name(
    'shared.storageTest.index',
);


/*
|--------------------------------------------------------------------------
| Upload
|--------------------------------------------------------------------------
*/

Route::post(
    '/storageTest/upload',
    [StorageTestController::class, 'upload'],
)->name(
    'shared.storageTest.upload',
);


/*
|--------------------------------------------------------------------------
| Upload Many
|--------------------------------------------------------------------------
*/

Route::post(
    '/storageTest/upload-many',
    [StorageTestController::class, 'uploadMany'],
)->name(
    'shared.storageTest.uploadMany',
);


/*
|--------------------------------------------------------------------------
| Download
|--------------------------------------------------------------------------
*/

Route::get(
    '/storageTest/{reference}/download',
    [StorageTestController::class, 'download'],
)->name(
    'shared.storageTest.download',
);


/*
|--------------------------------------------------------------------------
| Download Many
|--------------------------------------------------------------------------
*/

Route::post(
    '/storageTest/download-many',
    [StorageTestController::class, 'downloadMany'],
)->name(
    'shared.storageTest.downloadMany',
);


/*
|--------------------------------------------------------------------------
| View
|--------------------------------------------------------------------------
*/

Route::get(
    '/storageTest/{reference}/view',
    [StorageTestController::class, 'view'],
)->name(
    'shared.storageTest.view',
);


/*
|--------------------------------------------------------------------------
| Metadata
|--------------------------------------------------------------------------
*/

Route::get(
    '/storageTest/{reference}/metadata',
    [StorageTestController::class, 'metadata'],
)->name(
    'shared.storageTest.metadata',
);


/*
|--------------------------------------------------------------------------
| Find
|--------------------------------------------------------------------------
*/

Route::get(
    '/storageTest/{reference}/find',
    [StorageTestController::class, 'find'],
)->name(
    'shared.storageTest.find',
);

/*
|--------------------------------------------------------------------------
| Delete Many
|--------------------------------------------------------------------------
*/

Route::delete(
    '/storageTest/delete-many',
    [StorageTestController::class, 'deleteMany'],
)->name(
    'shared.storageTest.deleteMany',
);


/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

Route::delete(
    '/storageTest/{reference}',
    [StorageTestController::class, 'delete'],
)->name(
    'shared.storageTest.delete',
);

/*
|--------------------------------------------------------------------------
| Make Public
|--------------------------------------------------------------------------
*/

Route::patch(
    '/storageTest/{reference}/public',
    [StorageTestController::class, 'makePublic'],
)->name(
    'shared.storageTest.makePublic',
);


/*
|--------------------------------------------------------------------------
| Make Protected
|--------------------------------------------------------------------------
*/

Route::patch(
    '/storageTest/{reference}/protected',
    [StorageTestController::class, 'makeProtected'],
)->name(
    'shared.storageTest.makeProtected',
);


/*
|--------------------------------------------------------------------------
| Route Access
|--------------------------------------------------------------------------
|
| Define the access rules for the routes in this workflow.
|
*/

$accessRoutes = [];

McfRouteDataRegistry::register(
    $accessRoutes,
);
