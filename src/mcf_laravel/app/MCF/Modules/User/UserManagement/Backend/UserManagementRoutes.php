<?php

use App\MCF\AccessControl\Data\RoleData;
use App\MCF\AccessControl\Data\RoleRouteAccess;
use App\MCF\AccessControl\Registry\McfRouteDataRegistry;
use App\MCF\Modules\User\UserManagement\Backend\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/userManagement', [UserManagementController::class, 'index'])
    ->name('user.userManagement.index');

Route::post('/userManagement/{user}/disable', [UserManagementController::class, 'disable'])
    ->name('user.userManagement.disable');

Route::post('/userManagement/{user}/enable', [UserManagementController::class, 'enable'])
    ->name('user.userManagement.enable');

Route::post('/userManagement/{user}/delete', [UserManagementController::class, 'delete'])
    ->name('user.userManagement.delete');

Route::post('/userManagement/{user}/restore', [UserManagementController::class, 'restore'])
    ->withTrashed()->name('user.userManagement.restore');

$dataRouteList = [
    new RoleRouteAccess(
        routeNames: [
            'user.userManagement.index',
            'user.userManagement.disable',
            'user.userManagement.enable',
            'user.userManagement.delete',
            'user.userManagement.restore',
        ],
        roles: [
            new RoleData(role: 1),
        ],
    ),
];

McfRouteDataRegistry::register($dataRouteList);
