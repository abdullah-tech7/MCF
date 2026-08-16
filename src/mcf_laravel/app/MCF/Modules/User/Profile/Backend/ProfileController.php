<?php

declare (strict_types = 1);

namespace App\MCF\Modules\User\Profile\Backend;

use App\MCF\Base\MfcController;
use App\MCF\Modules\User\Profile\Backend\Request\UpdateEmailRequest;
use App\MCF\Modules\User\Profile\Backend\Request\UpdatePasswordRequest;
use App\MCF\Modules\User\Profile\Backend\Request\VerifyUpdateEmailRequest;
use App\MCF\Modules\User\Profile\Backend\Request\VerifyDeleteAccountRequest;
use App\MCF\Result\Authentication\ChangePasswordResult;
use App\MCF\Result\Authentication\SendVerificationResult;
use App\MCF\Result\Authentication\UpdateResult;
use App\MCF\Result\Authentication\VerificationResult;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class ProfileController extends MfcController
{
    public function __construct(
        protected ProfileService $service,
    ) {
    }

    /*
     |--------------------------------------------------------------------------
     | Profile
     |--------------------------------------------------------------------------
     */

    /**
     * Display the profile page.
     */
    public function index(): View
    {

        return view(
            'User::Profile.index',
            [
                'user' => $this->service->user(),
            ],
        );
    }

    /*
     |--------------------------------------------------------------------------
     | Update Password
     |--------------------------------------------------------------------------
     */

    /**
     * Display the update password page.
     */
    public function updatePassword(): View
    {
        return view(
            'User::Profile.updatePassword',
        );
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePasswordPost(
        UpdatePasswordRequest $request,
    ): RedirectResponse {
        $data = $request->getData();

        $result = $this->service->changePassword(
            $data->current_password,
            $data->password,
        );

        if (
            $result->is(
                ChangePasswordResult::UPDATED,
            )
        ) {
            return redirect()
                ->route('user.profile.index')
                ->with(
                    'success',
                    __('Password updated successfully.'),
                );
        }

        if (
            $result->is(
                ChangePasswordResult::INVALID_CURRENT_PASSWORD,
            )
        ) {
            return back()->with(
                'error',
                __('The current password is incorrect.'),
            );
        }

        if (
            $result->is(
                ChangePasswordResult::SAME_PASSWORD,
            )
        ) {
            return back()->with(
                'error',
                __('The new password must be different from your current password.'),
            );
        }

        return back()->with(
            'error',
            __('Unable to update your password. Please try again.'),
        );
    }

    /*
     |--------------------------------------------------------------------------
     | Update Email
     |--------------------------------------------------------------------------
     */

    /**
     * Display the update email page.
     */
    public function updateEmail(): View
    {
        return view(
            'User::Profile.updateEmail',
            [
                'user' => $this->service->user(),
            ],
        );
    }

    /**
     * Send a verification code to the new email address.
     */
    public function updateEmailPost(
        UpdateEmailRequest $request,
    ): RedirectResponse {
        $data = $request->getData();

        $result = $this->service->sendUpdateEmailCode(
            $data,
        );

        if (
            $result->is(
                SendVerificationResult::SENT,
            )
        ) {
            return redirect()->route(
                'user.profile.verifyUpdateEmail',
                [
                    'email' => $data->email,
                ],
            );
        }

        if (
            $result->is(
                SendVerificationResult::THROTTLED,
            )
        ) {
            return back()->with(
                'error',
                __(
                    'Too many verification attempts. Please try again later.',
                ),
            );
        }

        if (
            $result->is(
                SendVerificationResult::USER_NOT_FOUND,
            )
        ) {
            return back()->with(
                'error',
                __('Unable to find the authenticated user.'),
            );
        }

        if (
            $result->is(
                SendVerificationResult::SAME_TARGET,
            )
        ) {
            return back()->with(
                'error',
                __(
                    'The new email address must be different from your current email address.',
                ),
            );
        }

        return back()->with(
            'error',
            __(
                'Unable to send the verification code. Please try again.',
            ),
        );
    }

    /*
     |--------------------------------------------------------------------------
     | Verify Update Email
     |--------------------------------------------------------------------------
     */

    /**
     * Display the update email verification page.
     */
    public function verifyUpdateEmail(string $email): View | RedirectResponse
    {
        $verification = $this->service
            ->pendingUpdateEmailVerification($email);

        if ($verification === null) {
            return redirect()
                ->route('user.profile.updateEmail')
                ->with(
                    'error',
                    __('Please start the email update process first.'),
                );
        }

        $user = $this->service->user();

        return view(
            'User::Profile.verifyUpdateEmail',
            [
                'oldEmail'          => $user->getAttribute('email'),
                'newEmail'          => $verification->target,
                'cooldownRemaining' => $verification->cooldownRemaining,
            ],
        );
    }

    /**
     * Verify the new email address and complete the update.
     */
    public function verifyUpdateEmailPost(
        VerifyUpdateEmailRequest $request,
        string $email
    ): RedirectResponse {
        $data = $request->getData();

        $verification = $this->service
            ->pendingUpdateEmailVerification($email);

        if ($verification === null) {
            return redirect()
                ->route('user.profile.updateEmail')
                ->with(
                    'error',
                    __('Please start the email update process first.'),
                );
        }

        $verificationResult = $this->service->verifyUpdateEmail(
            $data,
            $verification->target,
        );

        if (
            $verificationResult->is(
                VerificationResult::VERIFIED,
            )
        ) {
            $result = $this->service->updateEmail(
                $verification->target,
            );

            if (
                $result->is(
                    UpdateResult::UPDATED,
                )
            ) {
                return redirect()
                    ->route('user.profile.index')
                    ->with(
                        'success',
                        __('Email address updated successfully.'),
                    );
            }

            if (
                $result->is(
                    UpdateResult::USER_NOT_FOUND,
                )
            ) {
                return back()->with(
                    'error',
                    __('Unable to find the authenticated user.'),
                );
            }

            return back()->with(
                'error',
                __('Unable to update the email address. Please try again.'),
            );
        }

        if (
            $verificationResult->is(
                VerificationResult::REQUEST_NOT_FOUND,
            )
        ) {
            return back()->with(
                'error',
                __('The verification request could not be found.'),
            );
        }

        if (
            $verificationResult->is(
                VerificationResult::EXPIRED,
            )
        ) {
            return back()->with(
                'error',
                __('The verification code has expired. Please request a new code.'),
            );
        }

        if (
            $verificationResult->is(
                VerificationResult::INVALID_CODE,
            )
        ) {
            return back()->with(
                'error',
                __('The verification code is incorrect.'),
            );
        }

        if (
            $verificationResult->is(
                VerificationResult::INVALID_TOKEN,
            )
        ) {
            return back()->with(
                'error',
                __('The verification token is invalid.'),
            );
        }

        return back()->with(
            'error',
            __('Unable to verify the new email address. Please try again.'),
        );
    }

    /*
     |--------------------------------------------------------------------------
     | Delete Account
     |--------------------------------------------------------------------------
     */

    /**
     * Start the account deletion workflow.
     *
     * This only sends the verification code.
     * The account is not deleted here.
     */
    public function deleteAccountPost(): RedirectResponse
    {
        $result = $this->service->sendDeleteAccountCode();

        if (
            $result->is(
                SendVerificationResult::SENT,
            )
        ) {
            return redirect()->route(
                'user.profile.verifyDeleteAccount',
            );
        }

        if (
            $result->is(
                SendVerificationResult::THROTTLED,
            )
        ) {
            return back()->with(
                'error',
                __(
                    'Too many verification attempts. Please try again later.',
                ),
            );
        }

        if (
            $result->is(
                SendVerificationResult::USER_NOT_FOUND,
            )
        ) {
            return back()->with(
                'error',
                __('Unable to find the authenticated user.'),
            );
        }

        return back()->with(
            'error',
            __(
                'Unable to send the verification code. Please try again.',
            ),
        );
    }

    /**
     * Display the account deletion verification page.
     */
    public function verifyDeleteAccount(): View | RedirectResponse
    {
        $verification = $this->service
            ->pendingDeleteAccountVerification();

        if ($verification === null) {
            return redirect()
                ->route('user.profile.index')
                ->with(
                    'error',
                    __('Please start the account deletion process first.'),
                );
        }

        $user = $this->service->user();

        if ($user === null) {
            return redirect()
                ->route('user.profile.index')
                ->with(
                    'error',
                    __('Unable to find the authenticated user.'),
                );
        }

        return view(
            'User::Profile.verifyDeleteAccount',
            [
                'email'             => $user->getAttribute('email'),
                'cooldownRemaining' =>
                $verification->cooldownRemaining,
            ],
        );
    }

    /**
     * Verify the deletion code and delete the account.
     */
    public function verifyDeleteAccountPost(
        VerifyDeleteAccountRequest $request,
    ): RedirectResponse {
        $data = $request->getData();

        $user = $this->service->user();

        if ($user === null) {
            return redirect()
                ->route('user.profile.index')
                ->with(
                    'error',
                    __('Unable to find the authenticated user.'),
                );
        }

        $verification = $this->service
            ->pendingDeleteAccountVerification();

        if ($verification === null) {
            return redirect()
                ->route('user.profile.index')
                ->with(
                    'error',
                    __('Please start the account deletion process first.'),
                );
        }

        $verificationResult = $this->service->verifyDeleteAccount(
            (string) $user->getAttribute('email'),
            $data->code,
        );

        if (
            $verificationResult->is(
                VerificationResult::VERIFIED,
            )
        ) {
            $deleted = $this->service->deleteAccount();

            if ($deleted) {
                return redirect()
                    ->route('user.auth.login')
                    ->with(
                        'success',
                        __('Your account has been deleted successfully.'),
                    );
            }

            return back()->with(
                'error',
                __('Unable to delete your account. Please try again.'),
            );
        }

        if (
            $verificationResult->is(
                VerificationResult::REQUEST_NOT_FOUND,
            )
        ) {
            return back()->with(
                'error',
                __('The verification request could not be found.'),
            );
        }

        if (
            $verificationResult->is(
                VerificationResult::EXPIRED,
            )
        ) {
            return back()->with(
                'error',
                __('The verification code has expired. Please request a new code.'),
            );
        }

        if (
            $verificationResult->is(
                VerificationResult::INVALID_CODE,
            )
        ) {
            return back()->with(
                'error',
                __('The verification code is incorrect.'),
            );
        }

        return back()->with(
            'error',
            __('Unable to verify the account deletion request. Please try again.'),
        );
    }

    /**
     * Resend the account deletion verification code.
     */
    public function resendDeleteAccountVerification(): RedirectResponse
    {
        $result = $this->service
            ->resendDeleteAccountVerification();

        if (
            $result->is(
                SendVerificationResult::SENT,
            )
        ) {
            return redirect()
                ->route(
                    'user.profile.verifyDeleteAccount',
                )
                ->with(
                    'success',
                    __('A new verification code has been sent to your email address.'),
                );
        }

        if (
            $result->is(
                SendVerificationResult::THROTTLED,
            )
        ) {
            return back()->with(
                'error',
                __(
                    'Too many verification attempts. Please try again later.',
                ),
            );
        }

        if (
            $result->is(
                SendVerificationResult::USER_NOT_FOUND,
            )
        ) {
            return back()->with(
                'error',
                __('Unable to find the authenticated user.'),
            );
        }

        return back()->with(
            'error',
            __(
                'Unable to send the verification code. Please try again.',
            ),
        );
    }
}
