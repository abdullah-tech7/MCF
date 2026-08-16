<?php

declare (strict_types = 1);

namespace App\MCF\Modules\User\Profile\Backend;

use App\MCF\Authentication\Internal\Enum\VerificationChannel;
use App\MCF\Authentication\Internal\Enum\VerificationMethod;
use App\MCF\Authentication\Internal\Enum\VerificationType;
use App\MCF\Authentication\Internal\VerificationStateData;
use App\MCF\Authentication\McfAccount;
use App\MCF\Authentication\McfAuth;
use App\MCF\Authentication\McfVerification;
use App\MCF\Base\MfcService;
use App\MCF\Modules\User\Profile\Backend\Request\UpdateEmailData;
use App\MCF\Modules\User\Profile\Backend\Request\VerifyUpdateEmailData;
use App\MCF\Result\Authentication\SendVerificationResult;
use App\MCF\Result\McfResult;
use Illuminate\Contracts\Auth\Authenticatable;

final class ProfileService extends MfcService
{
    /**
     * Get the authenticated user.
     */
    public function user(): ?Authenticatable
    {
        return McfAuth::user();
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(
        string $currentPassword,
        string $newPassword,
    ): McfResult {
        return McfAuth::changePassword(
            $currentPassword,
            $newPassword,
        );
    }

    /**
     * Send a verification code to the new email address.
     */
    public function sendUpdateEmailCode(
        UpdateEmailData $data,
    ): McfResult {
        return McfVerification::sendNewEmailCode(
            (string) $this->user()->getAttribute('email'),
            $data->email,
        );
    }

    /**
     * Verify the new email address.
     */
    public function verifyUpdateEmail(
        VerifyUpdateEmailData $data,
        string $newEmail,
    ): McfResult {
        return McfVerification::verifyNewEmailCode(
            (string) $this->user()->getAttribute('email'),
            $newEmail,
            $data->code,
        );
    }

    /**
     * Update the authenticated user's email address.
     */
    public function updateEmail(
        string $email,
    ): McfResult {
        return McfAuth::updateNewEmailVerified(
            $email,
        );
    }

    /**
     * Get the pending email update verification state.
     */
    public function pendingUpdateEmailVerification(
        string $newEmail,
    ): ?VerificationStateData {
        return McfVerification::pendingVerification(
            VerificationType::UPDATE_EMAIL,
            VerificationChannel::EMAIL,
            VerificationMethod::CODE,
            $newEmail,
        );
    }

/*
     |--------------------------------------------------------------------------
     | Delete Account
     |--------------------------------------------------------------------------
     */

    /**
     * Send a verification code to the authenticated user's
     * current email address before account deletion.
     */
    public function sendDeleteAccountCode(): McfResult
    {
        $user = $this->user();

        if ($user === null) {
            return new SendVerificationResult(
                SendVerificationResult::USER_NOT_FOUND,
            );
        }

        return McfVerification::sendEmailCode(
            (string) $user->getAttribute('email'),
            VerificationType::DELETE_ACCOUNT,
        );
    }

    /**
     * Get the pending account deletion verification state.
     */
    public function pendingDeleteAccountVerification(): ?VerificationStateData
    {
        $user = $this->user();

        if ($user === null) {
            return null;
        }

        return McfVerification::pendingVerification(
            VerificationType::DELETE_ACCOUNT,
            VerificationChannel::EMAIL,
            VerificationMethod::CODE,
            (string) $user->getAttribute('email'),
        );
    }

    /**
     * Verify the account deletion code.
     */
    public function verifyDeleteAccount(
        string $email,
        string $code,
    ): McfResult {
        return McfVerification::verifyEmailCode(
            $email,
            VerificationType::DELETE_ACCOUNT,
            $code,
        );
    }

    /**
     * Delete the authenticated user's account.
     *
     * Verification must already have been completed
     * by the caller before invoking this method.
     */
    public function deleteAccount(): bool
    {
        return McfAccount::deleteMyAccount();
    }

    /**
     * Resend the account deletion verification code.
     */
    public function resendDeleteAccountVerification(): McfResult
    {
        return $this->sendDeleteAccountCode();
    }
}
