<?php

declare(strict_types=1);

use App\MCF\Modules\Shared\RealtimeTest\Backend\RealtimeTestController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/realtimeTest',
    [
        RealtimeTestController::class,
        'index',
    ],
)->name(
    'shared.realtimeTest.index',
);


Route::post(
    '/realtimeTest/notification',
    [
        RealtimeTestController::class,
        'addNotification',
    ],
)->name(
    'shared.realtimeTest.addNotification',
);


Route::patch(
    '/realtimeTest/notifications/{notification}/read',
    [
        RealtimeTestController::class,
        'markAsRead',
    ],
)->name(
    'shared.realtimeTest.markAsRead',
);


Route::patch(
    '/realtimeTest/notifications/read-all',
    [
        RealtimeTestController::class,
        'markAllAsRead',
    ],
)->name(
    'shared.realtimeTest.markAllAsRead',
);


Route::patch(
    '/realtimeTest/notifications/unread-all',
    [
        RealtimeTestController::class,
        'markAllAsUnread',
    ],
)->name(
    'shared.realtimeTest.markAllAsUnread',
);


Route::delete(
    '/realtimeTest/notifications/{notification}',
    [
        RealtimeTestController::class,
        'delete',
    ],
)->name(
    'shared.realtimeTest.delete',
);


Route::delete(
    '/realtimeTest/notifications',
    [
        RealtimeTestController::class,
        'deleteAll',
    ],
)->name(
    'shared.realtimeTest.deleteAll',
);
