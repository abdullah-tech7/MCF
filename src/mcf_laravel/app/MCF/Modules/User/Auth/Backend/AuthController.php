<?php

declare (strict_types = 1);

namespace App\MCF\Modules\User\Auth\Backend;

use App\MCF\Authentication\Internal\Enum\VerificationType;
use App\MCF\Authentication\McfVerification;
use App\MCF\Base\MfcController;
use App\MCF\Modules\User\Auth\Backend\Request\ForgotPasswordRequest;
use App\MCF\Modules\User\Auth\Backend\Request\LoginRequest;
use App\MCF\Modules\User\Auth\Backend\Request\RegisterRequest;
use App\MCF\Modules\User\Auth\Backend\Request\ResetPasswordRequest;
use App\MCF\Modules\User\Auth\Backend\Request\RestoreAccountRequest;
use App\MCF\Modules\User\Auth\Backend\Request\VerifyEmailRequest;
use App\MCF\Modules\User\Auth\Backend\Request\VerifyForgotPasswordRequest;
use App\MCF\Modules\User\Auth\Backend\Request\VerifyRestoreAccountRequest;
use App\MCF\Result\Authentication\AuthenticationResult;
use App\MCF\Result\Authentication\SendVerificationResult;
use App\MCF\Result\Authentication\UpdateResult;
use App\MCF\Result\Authentication\VerificationResult;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class AuthController extends MfcController
{
    public function __construct(
        protected AuthService $service,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    */

    public function register(): View
    {
        return view(
            'User::Auth.register',
        );
    }

    public function registerPost(
        RegisterRequest $request,
    ): RedirectResponse {
        $data = $request->getData();

        $result = $this->service->register(
            $data,
        );

        if (
            $result->is(
                AuthenticationResult::SUCCESS,
            )
        ) {
            return redirect('/');
        }

        if (
            $result->is(
                AuthenticationResult::NEED_EMAIL_VERIFICATION,
            )
        ) {
            $verificationResult = McfVerification::sendEmailCode(
                $data->email,
                VerificationType::VERIFY_EMAIL,
            );

            if (
                $verificationResult->is(
                    SendVerificationResult::SENT,
                )
            ) {
                return redirect()->route(
                    'user.auth.verifyEmail',
                    [
                        'email' => $data->email,
                    ],
                );
            }

            if (
                $verificationResult->is(
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

            return back()->with(
                'error',
                __(
                    'Unable to send the verification code. Please try again.',
                ),
            );
        }

        if (
            $result->is(
                AuthenticationResult::NEED_PHONE_VERIFICATION,
            )
        ) {
            return back()->with(
                'error',
                __(
                    'Phone verification is required.',
                ),
            );
        }

        if (
            $result->is(
                AuthenticationResult::THROTTLED,
            )
        ) {
            return back()->with(
                'error',
                __(
                    'Too many authentication attempts. Please try again later.',
                ),
            );
        }

        if (
            $result->is(
                AuthenticationResult::NOT_ALLOWED,
            )
        ) {
            return redirect()
                ->route('user.auth.login')
                ->with(
                    'success',
                    __(
                        'Your account has been created successfully, but it requires administrator activation before you can sign in.',
                    ),
                );
        }

        return back()->with(
            'error',
            __(
                'Registration failed. Please try again.',
            ),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login(): View
    {
        return view(
            'User::Auth.login',
        );
    }

    public function loginPost(
        LoginRequest $request,
    ): RedirectResponse {
        $data = $request->getData();

        $result = $this->service->login(
            $data,
        );

        if (
            $result->is(
                AuthenticationResult::SUCCESS,
            )
        ) {
            return redirect('/');
        }

        if (
            $result->is(
                AuthenticationResult::NEED_EMAIL_VERIFICATION,
            )
        ) {
            $verificationResult = McfVerification::sendEmailCode(
                $data->email,
                VerificationType::VERIFY_EMAIL,
            );

            if (
                $verificationResult->is(
                    SendVerificationResult::SENT,
                )
            ) {
                return redirect()->route(
                    'user.auth.verifyEmail',
                    [
                        'email' => $data->email,
                    ],
                );
            }

            if (
                $verificationResult->is(
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

            return back()->with(
                'error',
                __(
                    'Unable to send the verification code. Please try again.',
                ),
            );
        }

        if (
            $result->is(
                AuthenticationResult::NEED_PHONE_VERIFICATION,
            )
        ) {
            return back()->with(
                'error',
                __(
                    'Phone verification is required.',
                ),
            );
        }

        if (
            $result->is(
                AuthenticationResult::DELETED_BY_SELF_RESTORABLE,
            )
        ) {
            return redirect()->route(
                'user.auth.restoreAccount',
                [
                    'email' => $data->email,
                ],
            );
        }

        if (
            $result->is(
                AuthenticationResult::DELETED_BY_SELF_EXPIRED,
            )
        ) {
            return back()->with(
                'error',
                __(
                    'This account was deleted by you and its restoration period has expired.',
                ),
            );
        }

        if (
            $result->is(
                AuthenticationResult::DELETED_BY_ACTOR,
            )
        ) {
            return back()->with(
                'error',
                __(
                    'This account has been deleted and cannot be used to sign in.',
                ),
            );
        }

        if (
            $result->is(
                AuthenticationResult::INVALID_CREDENTIALS,
            )
        ) {
            return back()->with(
                'error',
                __(
                    'Invalid email or password.',
                ),
            );
        }

        if (
            $result->is(
                AuthenticationResult::THROTTLED,
            )
        ) {
            return back()->with(
                'error',
                __(
                    'Too many login attempts. Please try again later.',
                ),
            );
        }

        if (
            $result->is(
                AuthenticationResult::NOT_ALLOWED,
            )
        ) {
            return back()->with(
                'error',
                __(
                    'Your account is inactive or you are not allowed to sign in.',
                ),
            );
        }

        return back()->with(
            'error',
            __(
                'Login failed. Please try again.',
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
    /*
 |--------------------------------------------------------------------------
 | Verify Email
 |--------------------------------------------------------------------------
 */

/**
 * Display the email verification page.
 */
    public function verifyEmail(
        string $email,
    ): View | RedirectResponse {
        $verification = $this->service
            ->pendingEmailVerification(
                $email,
            );

        if ($verification === null) {
            return redirect()
                ->route('user.auth.login')
                ->with(
                    'error',
                    __('Please start the email verification process first.'),
                );
        }

        return view(
            'User::Auth.verifyEmail',
            [
                'email'             => $verification->target,
                'cooldownRemaining' => $verification->cooldownRemaining,
            ],
        );
    }

/**
 * Verify the email address.
 */
    public function verifyEmailPost(
        VerifyEmailRequest $request,
        string $email,
    ): RedirectResponse {
        $data = $request->getData();

        $verification = $this->service
            ->pendingEmailVerification(
                $email,
            );

        if ($verification === null) {
            return redirect()
                ->route(
                    'user.auth.verifyEmail',
                    [
                        'email' => $email,
                    ],
                )
                ->with(
                    'error',
                    __('Please start the email verification process first.'),
                );
        }

        $result = $this->service->verifyEmail(
            $data,
            $verification->target,
        );

        if (
            $result->is(
                VerificationResult::VERIFIED,
            )
        ) {

            /*
         * LoginByVerifiedEmail
         */

            $result = $this->service->loginByVerifiedEmail(
                $verification->target,
            );

            if (
                $result->is(
                    AuthenticationResult::SUCCESS,
                )
            ) {
                return redirect('/');
            }

            if (
                $result->is(
                    AuthenticationResult::NEED_EMAIL_VERIFICATION,
                )
            ) {
                $verificationResult = McfVerification::sendEmailCode(
                    $data->email,
                    VerificationType::VERIFY_EMAIL,
                );

                if (
                    $verificationResult->is(
                        SendVerificationResult::SENT,
                    )
                ) {
                    return redirect()->route(
                        'user.auth.verifyEmail',
                    );
                }

                if (
                    $verificationResult->is(
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

                return back()->with(
                    'error',
                    __(
                        'Unable to send the verification code. Please try again.',
                    ),
                );
            }

            if (
                $result->is(
                    AuthenticationResult::NEED_PHONE_VERIFICATION,
                )
            ) {
                return back()->with(
                    'error',
                    __(
                        'Phone verification is required.',
                    ),
                );
            }

            if (
                $result->is(
                    AuthenticationResult::THROTTLED,
                )
            ) {
                return back()->with(
                    'error',
                    __(
                        'Too many authentication attempts. Please try again later.',
                    ),
                );
            }

            if (
                $result->is(
                    AuthenticationResult::NOT_ALLOWED,
                )
            ) {
                return redirect()
                    ->route('user.auth.login')
                    ->with(
                        'success',
                        __(
                            'Your account has been created successfully, but it requires administrator activation before you can sign in.',
                        ),
                    );
            }

        }

        if (
            $result->is(
                VerificationResult::REQUEST_NOT_FOUND,
            )
        ) {
            return back()->with(
                'error',
                __('The verification request could not be found.'),
            );
        }

        if (
            $result->is(
                VerificationResult::EXPIRED,
            )
        ) {
            return back()->with(
                'error',
                __('The verification code has expired. Please request a new code.'),
            );
        }

        if (
            $result->is(
                VerificationResult::INVALID_CODE,
            )
        ) {
            return back()->with(
                'error',
                __('The verification code is incorrect.'),
            );
        }

        if (
            $result->is(
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
            __('Unable to verify your email address. Please try again.'),
        );
    }

    /**
     * Resend the email verification code.
     */
    public function resendEmailVerification(
        string $email,
    ): RedirectResponse {
        $result = $this->service->sendEmailVerificationCode(
            $email,
        );

        if (
            $result->is(
                SendVerificationResult::SENT,
            )
        ) {
            return redirect()->route(
                'user.auth.verifyEmail',
                [
                    'email' => $email,
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
                __('Too many verification attempts. Please try again later.'),
            );
        }

        if (
            $result->is(
                SendVerificationResult::USER_NOT_FOUND,
            )
        ) {
            return back()->with(
                'error',
                __('Unable to find the user.'),
            );
        }

        return back()->with(
            'error',
            __('Unable to send the verification code. Please try again.'),
        );
    }

    /*
|--------------------------------------------------------------------------
| Forgot Password
|--------------------------------------------------------------------------
*/

/**
 * Display the forgot password page.
 */
    public function forgotPassword(): View
    {
        return view(
            'User::Auth.forgotPassword',
        );
    }

/**
 * Send a password reset verification code.
 */
    public function forgotPasswordPost(
        ForgotPasswordRequest $request,
    ): RedirectResponse {
        $data = $request->getData();

        $result = $this->service->sendForgotPasswordCode(
            $data->email,
        );

        if (
            $result->is(
                SendVerificationResult::SENT,
            )
        ) {
            return redirect()->route(
                'user.auth.verifyForgotPassword',
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
                __('Unable to find an account with this email address.'),
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
 * Display the forgot password verification page.
 */
    public function verifyForgotPassword(
        string $email,
    ): View | RedirectResponse {
        $verification = $this->service
            ->pendingForgotPasswordVerification(
                $email,
            );

        if ($verification === null) {
            return redirect()
                ->route('user.auth.forgotPassword')
                ->with(
                    'error',
                    __('Please start the password reset process first.'),
                );
        }

        return view(
            'User::Auth.verifyForgotPassword',
            [
                'email'             => $verification->target,
                'cooldownRemaining' => $verification->cooldownRemaining,
            ],
        );
    }

/**
 * Verify the password reset verification code.
 */
    public function verifyForgotPasswordPost(
        VerifyForgotPasswordRequest $request,
        string $email,
    ): RedirectResponse {
        $data = $request->getData();

        $verification = $this->service
            ->pendingForgotPasswordVerification(
                $email,
            );

        if ($verification === null) {
            return redirect()
                ->route('user.auth.forgotPassword')
                ->with(
                    'error',
                    __('Please start the password reset process first.'),
                );
        }

        $result = $this->service->verifyForgotPassword(
            $data,
            $verification->target,
        );

        if (
            $result->is(
                VerificationResult::VERIFIED,
            )
        ) {
            return redirect()->route(
                'user.auth.resetPassword',
                [
                    'email' => $verification->target,
                ],
            );
        }

        if (
            $result->is(
                VerificationResult::REQUEST_NOT_FOUND,
            )
        ) {
            return back()->with(
                'error',
                __('The verification request could not be found.'),
            );
        }

        if (
            $result->is(
                VerificationResult::EXPIRED,
            )
        ) {
            return back()->with(
                'error',
                __('The verification code has expired. Please request a new code.'),
            );
        }

        if (
            $result->is(
                VerificationResult::INVALID_CODE,
            )
        ) {
            return back()->with(
                'error',
                __('The verification code is incorrect.'),
            );
        }

        if (
            $result->is(
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
            __('Unable to verify your email address. Please try again.'),
        );
    }

/**
 * Resend the password reset verification code.
 */
    public function resendForgotPasswordVerification(
        string $email,
    ): RedirectResponse {
        $result = $this->service->sendForgotPasswordCode(
            $email,
        );

        if (
            $result->is(
                SendVerificationResult::SENT,
            )
        ) {
            return redirect()->route(
                'user.auth.verifyForgotPassword',
                [
                    'email' => $email,
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
            return redirect()
                ->route('user.auth.forgotPassword')
                ->with(
                    'error',
                    __('Unable to find an account with this email address.'),
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
 * Display the reset password page.
 */
    public function resetPassword(
        string $email,
    ): View | RedirectResponse {
        /*
     * The verification request must still be valid
     * before allowing access to the reset password page.
     */
        $verification = $this->service
            ->verifiedForgotPasswordVerification(
                $email,
            );

        if ($verification === null) {
            return redirect()
                ->route('user.auth.forgotPassword')
                ->with(
                    'error',
                    __('The password reset request is no longer valid.'),
                );
        }

        return view(
            'User::Auth.resetPassword',
            [
                'email' => $verification->target,
            ],
        );
    }

/**
 * Reset the user's password.
 */
    public function resetPasswordPost(
        ResetPasswordRequest $request,
        string $email,
    ): RedirectResponse {
        $data = $request->getData();

        /*
     * Make sure the password reset verification
     * request is still valid before changing the password.
     */
        $verification = $this->service
            ->verifiedForgotPasswordVerification(
                $email,
            );

        if ($verification === null) {
            return redirect()
                ->route('user.auth.forgotPassword')
                ->with(
                    'error',
                    __('The password reset request is no longer valid.'),
                );
        }

        $result = $this->service->resetPassword(
            $verification->target,
            $data->password,
        );

        if (
            $result->is(
                UpdateResult::UPDATED,
            )
        ) {
            return redirect()
                ->route('user.auth.login')
                ->with(
                    'success',
                    __('Your password has been reset successfully.'),
                );
        }

        if (
            $result->is(
                UpdateResult::USER_NOT_FOUND,
            )
        ) {
            return redirect()
                ->route('user.auth.forgotPassword')
                ->with(
                    'error',
                    __('Unable to find the user account.'),
                );
        }

        return back()->with(
            'error',
            __('Unable to reset your password. Please try again.'),
        );
    }

    /*
|--------------------------------------------------------------------------
| Restore Account
|--------------------------------------------------------------------------
*/

/**
 * Display the account restoration page.
 */
    public function restoreAccount(
        string $email,
    ): View {
        return view(
            'User::Auth.restoreAccount',
            [
                'email' => $email,
            ],
        );
    }

/**
 * Send the account restoration verification code.
 */
    public function restoreAccountPost(
        RestoreAccountRequest $request,
        string $email,
    ): RedirectResponse {
        $result = $this->service
            ->sendRestoreAccountCode(
                $email,
            );

        if (
            $result->is(
                SendVerificationResult::SENT,
            )
        ) {
            return redirect()->route(
                'user.auth.verifyRestoreAccount',
                [
                    'email' => $email,
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
            return redirect()
                ->route('user.auth.login')
                ->with(
                    'error',
                    __('Unable to find the account.'),
                );
        }

        return back()->with(
            'error',
            __(
                'Unable to send the restoration code. Please try again.',
            ),
        );
    }

/**
 * Display the account restoration verification page.
 */
    public function verifyRestoreAccount(
        string $email,
    ): View | RedirectResponse {
        $verification = $this->service
            ->pendingRestoreAccountVerification(
                $email,
            );

        if ($verification === null) {
            return redirect()
                ->route(
                    'user.auth.restoreAccount',
                    [
                        'email' => $email,
                    ],
                )
                ->with(
                    'error',
                    __(
                        'Please start the account restoration process first.',
                    ),
                );
        }

        return view(
            'User::Auth.verifyRestoreAccount',
            [
                'email'             => $verification->target,
                'cooldownRemaining' => $verification->cooldownRemaining,
            ],
        );
    }

/**
 * Verify the restoration code and restore the account.
 */
    public function verifyRestoreAccountPost(
        VerifyRestoreAccountRequest $request,
        string $email,
    ): RedirectResponse {
        $data = $request->getData();

        $verification = $this->service
            ->pendingRestoreAccountVerification(
                $email,
            );

        if ($verification === null) {
            return redirect()
                ->route(
                    'user.auth.restoreAccount',
                    [
                        'email' => $email,
                    ],
                )
                ->with(
                    'error',
                    __(
                        'Please start the account restoration process first.',
                    ),
                );
        }

        $verificationResult = $this->service
            ->verifyRestoreAccount(
                $data,
                $verification->target,
            );

        if (
            $verificationResult->is(
                VerificationResult::VERIFIED,
            )
        ) {
            $result = $this->service
                ->restoreAccount(
                    $verification->target,
                );

            if ($result) {
                return redirect()
                    ->route('user.auth.login')
                    ->with(
                        'success',
                        __('Your account has been restored successfully. You can now sign in.'),
                    );
            }

            return back()->with(
                'error',
                __(
                    'Unable to restore your account. The restoration period may have expired.',
                ),
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
                __(
                    'The verification code has expired. Please request a new code.',
                ),
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
            __('Unable to verify the account restoration request. Please try again.'),
        );
    }

/**
 * Resend the account restoration verification code.
 */
    public function resendRestoreAccountVerification(
        string $email,
    ): RedirectResponse {
        $result = $this->service
            ->sendRestoreAccountCode(
                $email,
            );

        if (
            $result->is(
                SendVerificationResult::SENT,
            )
        ) {
            return redirect()->route(
                'user.auth.verifyRestoreAccount',
                [
                    'email' => $email,
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
            return redirect()
                ->route('user.auth.login')
                ->with(
                    'error',
                    __('Unable to find the account.'),
                );
        }

        return back()->with(
            'error',
            __('Unable to send the restoration code. Please try again.'),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(): RedirectResponse
    {
        $this->service->logout();

        return redirect('/');
    }
}
