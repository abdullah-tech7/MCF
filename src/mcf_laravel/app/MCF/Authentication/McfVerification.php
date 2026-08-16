<?php

declare (strict_types = 1);

namespace App\MCF\Authentication;

use App\MCF\Authentication\Internal\Enum\VerificationChannel;
use App\MCF\Authentication\Internal\Enum\VerificationMethod;
use App\MCF\Authentication\Internal\Enum\VerificationType;
use App\MCF\Authentication\Internal\UserService;
use App\MCF\Authentication\Internal\VerificationService;
use App\MCF\Authentication\Internal\VerificationStateData;
use App\MCF\Mail\Authentication\ChangeEmailCodeMail;
use App\MCF\Mail\Authentication\DeleteAccountCodeMail;
use App\MCF\Mail\Authentication\ResetPasswordCodeMail;
use App\MCF\Mail\Authentication\ResetPasswordLinkMail;
use App\MCF\Mail\Authentication\RestoreAccountCodeMail;
use App\MCF\Mail\Authentication\VerifyEmailCodeMail;
use App\MCF\Mail\Authentication\VerifyEmailLinkMail;
use App\MCF\Mail\McfMail;
use App\MCF\Result\Authentication\SendVerificationResult;
use App\MCF\Result\Authentication\VerificationResult;
use App\MCF\Result\McfResult;
use App\MCF\Sms\McfSms;
use App\MCF\Sms\SmsMessages;
use InvalidArgumentException;

final class McfVerification
{
    private function __construct()
    {
    }

    /*
     * |--------------------------------------------------------------------------
     * | Email Verification
     * |--------------------------------------------------------------------------
     */

    public static function sendEmailCode(
        string $email,
        VerificationType $type,
    ): McfResult {
        $user = $type === VerificationType::RESTORE_ACCOUNT
            ? UserService::findUserWithTrashedByEmail($email)
            : UserService::findUserByEmail($email);

        if ($user === null) {
            return new SendVerificationResult(
                SendVerificationResult::USER_NOT_FOUND,
            );
        }

        return self::sendEmailCodeForUser(
            (int) $user->getAuthIdentifier(),
            $email,
            $type,
        );
    }

    public static function sendEmailLink(
        string $email,
        VerificationType $type,
        string $url,
    ): McfResult {
        $user = $type === VerificationType::RESTORE_ACCOUNT
            ? UserService::findUserWithTrashedByEmail($email)
            : UserService::findUserByEmail($email);

        if ($user === null) {
            return new SendVerificationResult(
                SendVerificationResult::USER_NOT_FOUND,
            );
        }

        $userId = (int) $user->getAuthIdentifier();

        try {
            $verification = VerificationService::prepareVerificationRequest(
                $userId,
                $type,
                VerificationChannel::EMAIL,
                VerificationMethod::LINK,
                $email,
            );

            if ($verification === null) {
                return new SendVerificationResult(
                    SendVerificationResult::THROTTLED,
                );
            }

            $mail = match ($type) {
                VerificationType::VERIFY_EMAIL   =>
                new VerifyEmailLinkMail($url),

                VerificationType::RESET_PASSWORD =>
                new ResetPasswordLinkMail($url),

                default                          => throw new InvalidArgumentException(
                    'The verification type does not support email link verification.',
                ),
            };

            McfMail::send(
                $email,
                $mail,
            );

            return new SendVerificationResult(
                SendVerificationResult::SENT,
            );
        } catch (\Throwable) {
            return new SendVerificationResult(
                SendVerificationResult::FAILED,
            );
        }
    }

    /*
     * |--------------------------------------------------------------------------
     * | New Email
     * |--------------------------------------------------------------------------
     */

    public static function sendNewEmailCode(
        string $oldEmail,
        string $newEmail,
    ): McfResult {
        $user = McfAuth::user();

        if ($user === null) {
            return new SendVerificationResult(
                SendVerificationResult::USER_NOT_FOUND,
            );
        }

        if ($user->getAttribute('email') !== $oldEmail) {
            return new SendVerificationResult(
                SendVerificationResult::USER_NOT_FOUND,
            );
        }

        if ($oldEmail === $newEmail) {
            return new SendVerificationResult(
                SendVerificationResult::SAME_TARGET,
            );
        }

        return self::sendEmailCodeForUser(
            (int) $user->getAuthIdentifier(),
            $newEmail,
            VerificationType::UPDATE_EMAIL,
        );
    }

    private static function sendEmailCodeForUser(
        int $userId,
        string $email,
        VerificationType $type,
    ): McfResult {
        try {
            $verification = VerificationService::prepareVerificationRequest(
                $userId,
                $type,
                VerificationChannel::EMAIL,
                VerificationMethod::CODE,
                $email,
            );

            if ($verification === null) {
                return new SendVerificationResult(
                    SendVerificationResult::THROTTLED,
                );
            }

            $mail = match ($type) {
                VerificationType::VERIFY_EMAIL    =>
                new VerifyEmailCodeMail(
                    $verification['value'],
                ),

                VerificationType::RESET_PASSWORD  =>
                new ResetPasswordCodeMail(
                    $verification['value'],
                ),

                VerificationType::UPDATE_EMAIL    =>
                new ChangeEmailCodeMail(
                    $verification['value'],
                ),

                VerificationType::DELETE_ACCOUNT  =>
                new DeleteAccountCodeMail(
                    $verification['value'],
                ),

                VerificationType::RESTORE_ACCOUNT =>
                new RestoreAccountCodeMail(
                    $verification['value'],
                ),

                default                           => throw new InvalidArgumentException(
                    'The verification type does not support email code verification.',
                ),
            };

            McfMail::send(
                $email,
                $mail,
            );

            return new SendVerificationResult(
                SendVerificationResult::SENT,
            );
        } catch (\Throwable) {
            return new SendVerificationResult(
                SendVerificationResult::FAILED,
            );
        }
    }

    /*
     * |--------------------------------------------------------------------------
     * | Phone Verification
     * |--------------------------------------------------------------------------
     */

    public static function sendPhoneCode(
        string $phone,
        VerificationType $type,
    ): McfResult {
        $user = $type === VerificationType::RESTORE_ACCOUNT
            ? UserService::findUserWithTrashedByPhone($phone)
            : UserService::findUserByPhone($phone);

        if ($user === null) {
            return new SendVerificationResult(
                SendVerificationResult::USER_NOT_FOUND,
            );
        }

        return self::sendPhoneCodeForUser(
            (int) $user->getAuthIdentifier(),
            $phone,
            $type,
        );
    }

    /*
     * |--------------------------------------------------------------------------
     * | New Phone
     * |--------------------------------------------------------------------------
     */

    public static function sendNewPhoneCode(
        string $oldPhone,
        string $newPhone,
    ): McfResult {
        $user = McfAuth::user();

        if ($user === null) {
            return new SendVerificationResult(
                SendVerificationResult::USER_NOT_FOUND,
            );
        }

        if ($user->getAttribute('phone') !== $oldPhone) {
            return new SendVerificationResult(
                SendVerificationResult::USER_NOT_FOUND,
            );
        }

        if ($oldPhone === $newPhone) {
            return new SendVerificationResult(
                SendVerificationResult::SAME_TARGET,
            );
        }

        return self::sendPhoneCodeForUser(
            (int) $user->getAuthIdentifier(),
            $newPhone,
            VerificationType::UPDATE_PHONE,
        );
    }

    private static function sendPhoneCodeForUser(
        int $userId,
        string $phone,
        VerificationType $type,
    ): McfResult {
        try {
            $verification = VerificationService::prepareVerificationRequest(
                $userId,
                $type,
                VerificationChannel::PHONE,
                VerificationMethod::CODE,
                $phone,
            );

            if ($verification === null) {
                return new SendVerificationResult(
                    SendVerificationResult::THROTTLED,
                );
            }

            $message = match ($type) {
                VerificationType::VERIFY_PHONE   =>
                SmsMessages::verifyPhone(
                    $verification['value'],
                ),

                VerificationType::RESET_PASSWORD =>
                SmsMessages::resetPassword(
                    $verification['value'],
                ),

                VerificationType::UPDATE_PHONE   =>
                SmsMessages::changePhone(
                    $verification['value'],
                ),

                default                          => throw new InvalidArgumentException(
                    'The verification type does not support phone code verification.',
                ),
            };

            McfSms::send(
                $phone,
                $message,
            );

            return new SendVerificationResult(
                SendVerificationResult::SENT,
            );
        } catch (\Throwable) {
            return new SendVerificationResult(
                SendVerificationResult::FAILED,
            );
        }
    }

    /*
     * |--------------------------------------------------------------------------
     * | Verify Email
     * |--------------------------------------------------------------------------
     */

    public static function verifyEmailCode(
        string $email,
        VerificationType $type,
        string $code,
    ): McfResult {
        return self::verifyCode(
            $type,
            VerificationChannel::EMAIL,
            $email,
            $code,
        );
    }

    public static function verifyEmailLink(
        string $email,
        VerificationType $type,
        string $token,
    ): McfResult {
        return self::verifyToken(
            $type,
            VerificationChannel::EMAIL,
            $email,
            $token,
        );
    }

    /*
     * |--------------------------------------------------------------------------
     * | New Email
     * |--------------------------------------------------------------------------
     */

    public static function verifyNewEmailCode(
        string $oldEmail,
        string $newEmail,
        string $code,
    ): McfResult {
        $user = McfAuth::user();

        if ($user === null) {
            return new VerificationResult(
                VerificationResult::USER_NOT_FOUND,
            );
        }

        if ($user->getAttribute('email') !== $oldEmail) {
            return new VerificationResult(
                VerificationResult::USER_NOT_FOUND,
            );
        }

        return self::verifyCodeForUser(
            (int) $user->getAuthIdentifier(),
            VerificationType::UPDATE_EMAIL,
            VerificationChannel::EMAIL,
            $newEmail,
            $code,
        );
    }

    /*
     * |--------------------------------------------------------------------------
     * | Verify Phone
     * |--------------------------------------------------------------------------
     */

    public static function verifyPhoneCode(
        string $phone,
        VerificationType $type,
        string $code,
    ): McfResult {
        return self::verifyCode(
            $type,
            VerificationChannel::PHONE,
            $phone,
            $code,
        );
    }

    /*
     * |--------------------------------------------------------------------------
     * | New Phone
     * |--------------------------------------------------------------------------
     */

    public static function verifyNewPhoneCode(
        string $oldPhone,
        string $newPhone,
        string $code,
    ): McfResult {
        $user = McfAuth::user();

        if ($user === null) {
            return new VerificationResult(
                VerificationResult::USER_NOT_FOUND,
            );
        }

        if ($user->getAttribute('phone') !== $oldPhone) {
            return new VerificationResult(
                VerificationResult::USER_NOT_FOUND,
            );
        }

        return self::verifyCodeForUser(
            (int) $user->getAuthIdentifier(),
            VerificationType::UPDATE_PHONE,
            VerificationChannel::PHONE,
            $newPhone,
            $code,
        );
    }

    /*
     * |--------------------------------------------------------------------------
     * | Private Verification
     * |--------------------------------------------------------------------------
     */

    private static function verifyCode(
        VerificationType $type,
        VerificationChannel $channel,
        string $target,
        string $code,
    ): McfResult {
        try {
            $request = VerificationService::findVerificationRequest(
                $type,
                $channel,
                VerificationMethod::CODE,
                $target,
            );

            if ($request === null) {
                return new VerificationResult(
                    VerificationResult::REQUEST_NOT_FOUND,
                );
            }

            if ($request->expires_at->isPast()) {
                return new VerificationResult(
                    VerificationResult::EXPIRED,
                );
            }

            if (
                ! VerificationService::validateVerificationCode(
                    $request,
                    $code,
                )
            ) {
                return new VerificationResult(
                    VerificationResult::INVALID_CODE,
                );
            }

            if (
                ! VerificationService::completeVerification(
                    $request,
                )
            ) {
                return new VerificationResult(
                    VerificationResult::FAILED,
                );
            }

            return new VerificationResult(
                VerificationResult::VERIFIED,
            );
        } catch (\Throwable) {
            return new VerificationResult(
                VerificationResult::FAILED,
            );
        }
    }

    private static function verifyCodeForUser(
        int $userId,
        VerificationType $type,
        VerificationChannel $channel,
        string $target,
        string $code,
    ): McfResult {
        try {
            $request = VerificationService::findVerificationRequestForUser(
                $userId,
                $type,
                $channel,
                VerificationMethod::CODE,
                $target,
            );

            if ($request === null) {
                return new VerificationResult(
                    VerificationResult::REQUEST_NOT_FOUND,
                );
            }

            if ($request->expires_at->isPast()) {
                return new VerificationResult(
                    VerificationResult::EXPIRED,
                );
            }

            if (
                ! VerificationService::validateVerificationCode(
                    $request,
                    $code,
                )
            ) {
                return new VerificationResult(
                    VerificationResult::INVALID_CODE,
                );
            }

            if (
                ! VerificationService::completeVerification(
                    $request,
                )
            ) {
                return new VerificationResult(
                    VerificationResult::FAILED,
                );
            }

            return new VerificationResult(
                VerificationResult::VERIFIED,
            );
        } catch (\Throwable) {
            return new VerificationResult(
                VerificationResult::FAILED,
            );
        }
    }

    private static function verifyToken(
        VerificationType $type,
        VerificationChannel $channel,
        string $target,
        string $token,
    ): McfResult {
        try {
            $request = VerificationService::findVerificationRequest(
                $type,
                $channel,
                VerificationMethod::LINK,
                $target,
            );

            if ($request === null) {
                return new VerificationResult(
                    VerificationResult::REQUEST_NOT_FOUND,
                );
            }

            if ($request->expires_at->isPast()) {
                return new VerificationResult(
                    VerificationResult::EXPIRED,
                );
            }

            if (
                ! VerificationService::validateVerificationToken(
                    $request,
                    $token,
                )
            ) {
                return new VerificationResult(
                    VerificationResult::INVALID_TOKEN,
                );
            }

            if (
                ! VerificationService::completeVerification(
                    $request,
                )
            ) {
                return new VerificationResult(
                    VerificationResult::FAILED,
                );
            }

            return new VerificationResult(
                VerificationResult::VERIFIED,
            );
        } catch (\Throwable) {
            return new VerificationResult(
                VerificationResult::FAILED,
            );
        }
    }

    /*
     * |--------------------------------------------------------------------------
     * | Pending Verification
     * |--------------------------------------------------------------------------
     */

    public static function pendingVerification(
        VerificationType $type,
        VerificationChannel $channel,
        VerificationMethod $method,
        string $target,
    ): ?VerificationStateData {
        $request = VerificationService::findActiveVerificationRequest(
            $type,
            $channel,
            $method,
            $target,
        );

        if ($request === null) {
            return null;
        }

        return new VerificationStateData(
            target: (string) $request->target,
            cooldownRemaining: VerificationService::cooldownRemaining(
                $request,
            ),
            sendAttempts: (int) $request->send_attempts,
            expiresAt: $request->expires_at,
        );
    }

    /*
     * |--------------------------------------------------------------------------
     * | Verified Verification
     * |--------------------------------------------------------------------------
     */

    public static function verifiedVerification(
        VerificationType $type,
        VerificationChannel $channel,
        VerificationMethod $method,
        string $target,
    ): ?VerificationStateData {
        $request = VerificationService::findVerifiedVerificationRequest(
            $type,
            $channel,
            $method,
            $target,
        );

        if ($request === null) {
            return null;
        }

        return new VerificationStateData(
            target: (string) $request->target,
            cooldownRemaining: VerificationService::cooldownRemaining(
                $request,
            ),
            sendAttempts: (int) $request->send_attempts,
            expiresAt: $request->expires_at,
        );
    }

    /*
     * |--------------------------------------------------------------------------
     * | Forgot Password
     * |--------------------------------------------------------------------------
     */

    public static function verifiedForgotPasswordVerification(
        string $email,
    ): ?VerificationStateData {
        $request = VerificationService::findVerifiedResetPasswordVerificationRequest(
            $email,
        );

        if ($request === null) {
            return null;
        }

        return new VerificationStateData(
            target: (string) $request->target,
            cooldownRemaining: VerificationService::cooldownRemaining(
                $request,
            ),
            sendAttempts: (int) $request->send_attempts,
            expiresAt: $request->expires_at,
        );
    }

    /**
     * Consume a verified password reset request.
     */
    public static function consumeForgotPasswordVerification(
        string $email,
    ): bool {
        $request = VerificationService::findVerifiedResetPasswordVerificationRequest(
            $email,
        );

        if ($request === null) {
            return false;
        }

        return VerificationService::revokeVerificationRequest(
            $request,
        );
    }
}
