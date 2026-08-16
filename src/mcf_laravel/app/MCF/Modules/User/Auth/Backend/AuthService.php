<?php

declare (strict_types = 1);

namespace App\MCF\Modules\User\Auth\Backend;

use App\MCF\Authentication\Internal\Enum\VerificationChannel;
use App\MCF\Authentication\Internal\Enum\VerificationMethod;
use App\MCF\Authentication\Internal\Enum\VerificationType;
use App\MCF\Authentication\Internal\VerificationStateData;
use App\MCF\Authentication\McfAccount;
use App\MCF\Authentication\McfAuth;
use App\MCF\Authentication\McfVerification;
use App\MCF\Base\MfcService;
use App\MCF\Modules\User\Auth\Backend\Request\LoginData;
use App\MCF\Modules\User\Auth\Backend\Request\RegisterData;
use App\MCF\Modules\User\Auth\Backend\Request\VerifyEmailData;
use App\MCF\Modules\User\Auth\Backend\Request\VerifyForgotPasswordData;
use App\MCF\Modules\User\Auth\Backend\Request\VerifyRestoreAccountData;
use App\MCF\Result\Authentication\AuthenticationResult;
use App\MCF\Result\McfResult;
use App\Models\User;

final class AuthService extends MfcService
{
    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    */

    public function register(
        RegisterData $data,
    ): McfResult {
        try {
            $user = $this->dataToModel(
                $data,
                new User(),
            );

            $user->password = McfAuth::hashPassword(
                $data->password,
            );

            //employee
            $user->role_id = 2;
            $user->save();
            $user->refresh();

            return McfAuth::loginByUser(
                $user,
            );
        } catch (\Throwable) {
            return new AuthenticationResult(
                AuthenticationResult::FAILED,
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login(
        LoginData $data,
    ): McfResult {
        return McfAuth::loginByCredentials(
            [
                'email'    => $data->email,
                'password' => $data->password,
            ],
            $data->remember,
        );
    }

    /**
     * Send a verification code to the email address.
     */
    public function sendEmailVerificationCode(
        string $email,
    ): McfResult {
        return McfVerification::sendEmailCode(
            $email,
            VerificationType::VERIFY_EMAIL,
        );
    }

/**
 * Verify the email address.
 */
    public function verifyEmail(
        VerifyEmailData $data,
        string $email,
    ): McfResult {
        return McfVerification::verifyEmailCode(
            $email,
            VerificationType::VERIFY_EMAIL,
            $data->code,
        );
    }

/**
 * Get the pending email verification state.
 */
    public function pendingEmailVerification(
        string $email,
    ): ?VerificationStateData {
        return McfVerification::pendingVerification(
            VerificationType::VERIFY_EMAIL,
            VerificationChannel::EMAIL,
            VerificationMethod::CODE,
            $email,
        );
    }

    /**
     * Complete email verification and authenticate the user.
     */
    public function loginByVerifiedEmail(
        string $email,
    ): McfResult {
        return McfAuth::loginByVerifiedUser(
            $email,
        );
    }

    /*
|--------------------------------------------------------------------------
| Forgot Password
|--------------------------------------------------------------------------
*/

/**
 * Send a password reset verification code to the email address.
 */
    public function sendForgotPasswordCode(
        string $email,
    ): McfResult {
        return McfVerification::sendEmailCode(
            $email,
            VerificationType::RESET_PASSWORD,
        );
    }

/**
 * Verify the password reset verification code.
 */
    public function verifyForgotPassword(
        VerifyForgotPasswordData $data,
        string $email,
    ): McfResult {
        return McfVerification::verifyEmailCode(
            $email,
            VerificationType::RESET_PASSWORD,
            $data->code,
        );
    }

/**
 * Get the pending password reset verification state.
 */
    public function pendingForgotPasswordVerification(
        string $email,
    ): ?VerificationStateData {
        return McfVerification::pendingVerification(
            VerificationType::RESET_PASSWORD,
            VerificationChannel::EMAIL,
            VerificationMethod::CODE,
            $email,
        );
    }

    /**
     * Reset the user's password after email verification.
     */
    public function resetPassword(
        string $email,
        string $password,
    ): McfResult {
        return McfAuth::resetPassword(
            $email,
            $password,
        );
    }

    public function verifiedForgotPasswordVerification(
        string $email,
    ): ?VerificationStateData {
        return McfVerification::verifiedVerification(
            VerificationType::RESET_PASSWORD,
            VerificationChannel::EMAIL,
            VerificationMethod::CODE,
            $email,
        );
    }

    /*
|--------------------------------------------------------------------------
| Restore Account
|--------------------------------------------------------------------------
*/

/**
 * Send a verification code to restore a self-deleted account.
 */
    public function sendRestoreAccountCode(
        string $email,
    ): McfResult {
        return McfVerification::sendEmailCode(
            $email,
            VerificationType::RESTORE_ACCOUNT,
        );
    }

/**
 * Get the pending account restoration verification state.
 */
    public function pendingRestoreAccountVerification(
        string $email,
    ): ?VerificationStateData {
        return McfVerification::pendingVerification(
            VerificationType::RESTORE_ACCOUNT,
            VerificationChannel::EMAIL,
            VerificationMethod::CODE,
            $email,
        );
    }

/**
 * Verify the account restoration code.
 */
    public function verifyRestoreAccount(
        VerifyRestoreAccountData $data,
        string $email,
    ): McfResult {
        return McfVerification::verifyEmailCode(
            $email,
            VerificationType::RESTORE_ACCOUNT,
            $data->code,
        );
    }

/**
 * Restore the deleted account after successful verification.
 */
    public function restoreAccount(
        string $email,
    ): bool {
        return McfAccount::restoreMyAccount(
            $email,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(): void
    {
        McfAuth::logout();
    }
}
