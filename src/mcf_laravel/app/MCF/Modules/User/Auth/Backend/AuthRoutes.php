<?php

use App\MCF\AccessControl\Data\AuthRouteAccess;
use App\MCF\AccessControl\Data\GuestRouteAccess;
use App\MCF\AccessControl\Registry\McfRouteDataRegistry;
use App\MCF\Modules\User\Auth\Backend\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/register', [AuthController::class, 'register'])
    ->name('user.auth.register');

Route::post('/registerpost', [AuthController::class, 'registerPost'])
    ->name('user.auth.registerPost');

Route::get('/login', [AuthController::class, 'login'])
    ->name('user.auth.login');

Route::post('/loginpost', [AuthController::class, 'loginPost'])
    ->name('user.auth.loginPost');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('user.auth.logout');

/*
|--------------------------------------------------------------------------
| Verify Email
|--------------------------------------------------------------------------
*/

Route::get(
    '/verifyemail/{email}',
    [AuthController::class, 'verifyEmail'],
)->name('user.auth.verifyEmail');

Route::post(
    '/verifyemailpost/{email}',
    [AuthController::class, 'verifyEmailPost'],
)->name('user.auth.verifyEmailPost');

Route::post(
    '/verifyemail/{email}/resend',
    [AuthController::class, 'resendEmailVerification'],
)->name('user.auth.resendEmailVerification');

/*
|--------------------------------------------------------------------------
| Forgot Password
|--------------------------------------------------------------------------
*/

Route::get(
    '/forgotpassword',
    [AuthController::class, 'forgotPassword'],
)->name('user.auth.forgotPassword');

Route::post(
    '/forgotpasswordpost',
    [AuthController::class, 'forgotPasswordPost'],
)->name('user.auth.forgotPasswordPost');

Route::get(
    '/verifyforgotpassword/{email}',
    [AuthController::class, 'verifyForgotPassword'],
)->name('user.auth.verifyForgotPassword');

Route::post(
    '/verifyforgotpasswordpost/{email}',
    [AuthController::class, 'verifyForgotPasswordPost'],
)->name('user.auth.verifyForgotPasswordPost');

Route::post(
    '/verifyforgotpassword/{email}/resend',
    [AuthController::class, 'resendForgotPasswordVerification'],
)->name('user.auth.resendForgotPasswordVerification');

Route::get(
    '/resetpassword/{email}',
    [AuthController::class, 'resetPassword'],
)->name('user.auth.resetPassword');

Route::post(
    '/resetpasswordpost/{email}',
    [AuthController::class, 'resetPasswordPost'],
)->name('user.auth.resetPasswordPost');

/*
|--------------------------------------------------------------------------
| Restore Account
|--------------------------------------------------------------------------
*/

Route::get(
    '/restoreaccount/{email}',
    [AuthController::class, 'restoreAccount'],
)->name('user.auth.restoreAccount');

Route::post(
    '/restoreaccount/{email}',
    [AuthController::class, 'restoreAccountPost'],
)->name('user.auth.restoreAccountPost');

Route::get(
    '/verifyrestoreaccount/{email}',
    [AuthController::class, 'verifyRestoreAccount'],
)->name('user.auth.verifyRestoreAccount');

Route::post(
    '/verifyrestoreaccountpost/{email}',
    [AuthController::class, 'verifyRestoreAccountPost'],
)->name('user.auth.verifyRestoreAccountPost');

Route::post(
    '/verifyrestoreaccount/{email}/resend',
    [AuthController::class, 'resendRestoreAccountVerification'],
)->name('user.auth.resendRestoreAccountVerification');

/*
|--------------------------------------------------------------------------
| Access
|--------------------------------------------------------------------------
*/

$dataRouteList = [

    new GuestRouteAccess(
        routeNames: [
            'user.auth.register',
            'user.auth.registerPost',
            'user.auth.login',
            'user.auth.loginPost',
            'user.auth.verifyEmail',
            'user.auth.verifyEmailPost',
            'user.auth.resendEmailVerification',
            'user.auth.forgotPassword',
            'user.auth.forgotPasswordPost',
            'user.auth.verifyForgotPassword',
            'user.auth.verifyForgotPasswordPost',
            'user.auth.resendForgotPasswordVerification',
            'user.auth.resetPassword',
            'user.auth.resetPasswordPost',
            'user.auth.restoreAccount',
            'user.auth.restoreAccountPost',
            'user.auth.verifyRestoreAccount',
            'user.auth.verifyRestoreAccountPost',
            'user.auth.resendRestoreAccountVerification',
        ],
    ),

    new AuthRouteAccess(
        routeNames: [
            'user.auth.logout',
        ],
    ),

];

McfRouteDataRegistry::register($dataRouteList);
