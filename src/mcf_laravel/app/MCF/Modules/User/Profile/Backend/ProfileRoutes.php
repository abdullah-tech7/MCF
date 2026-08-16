<?php

use App\MCF\AccessControl\Data\AuthRouteAccess;
use App\MCF\AccessControl\Registry\McfRouteDataRegistry;
use App\MCF\Modules\User\Profile\Backend\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::get(
    '/profile',
    [ProfileController::class, 'index'],
)->name('user.profile.index');


/*
|--------------------------------------------------------------------------
| Update Password
|--------------------------------------------------------------------------
*/

Route::get(
    '/updatepassword',
    [ProfileController::class, 'updatePassword'],
)->name('user.profile.updatePassword');

Route::post(
    '/updatepasswordpost',
    [ProfileController::class, 'updatePasswordPost'],
)->name('user.profile.updatePasswordPost');


/*
|--------------------------------------------------------------------------
| Update Email
|--------------------------------------------------------------------------
*/

Route::get(
    '/updateemail',
    [ProfileController::class, 'updateEmail'],
)->name('user.profile.updateEmail');

Route::post(
    '/updateemailpost',
    [ProfileController::class, 'updateEmailPost'],
)->name('user.profile.updateEmailPost');

Route::get(
    '/verifyupdateemail/{email}',
    [ProfileController::class, 'verifyUpdateEmail'],
)->name('user.profile.verifyUpdateEmail');

Route::post(
    '/verifyupdateemail/{email}',
    [ProfileController::class, 'verifyUpdateEmailPost'],
)->name('user.profile.verifyUpdateEmailPost');


/*
|--------------------------------------------------------------------------
| Delete Account
|--------------------------------------------------------------------------
*/

Route::post(
    '/deleteaccount',
    [ProfileController::class, 'deleteAccountPost'],
)->name('user.profile.deleteAccountPost');

Route::get(
    '/verifydeleteaccount',
    [ProfileController::class, 'verifyDeleteAccount'],
)->name('user.profile.verifyDeleteAccount');

Route::post(
    '/verifydeleteaccountpost',
    [ProfileController::class, 'verifyDeleteAccountPost'],
)->name('user.profile.verifyDeleteAccountPost');

Route::post(
    '/verifydeleteaccount/resend',
    [ProfileController::class, 'resendDeleteAccountVerification'],
)->name('user.profile.resendDeleteAccountVerification');

/*
|--------------------------------------------------------------------------
| Access Control
|--------------------------------------------------------------------------
*/

$dataRouteList = [

    new AuthRouteAccess(
        routeNames: [
            'user.profile.index',
            'user.profile.updatePassword',
            'user.profile.updatePasswordPost',
            'user.profile.updateEmail',
            'user.profile.updateEmailPost',
            'user.profile.verifyUpdateEmail',
            'user.profile.verifyUpdateEmailPost',
            'user.profile.deleteAccountPost',
            'user.profile.verifyDeleteAccount',
            'user.profile.verifyDeleteAccountPost',
            'user.profile.resendDeleteAccountVerification',
        ],
    ),

];

McfRouteDataRegistry::register(
    $dataRouteList,
);
