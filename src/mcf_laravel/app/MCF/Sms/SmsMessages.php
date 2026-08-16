<?php

declare(strict_types=1);

namespace App\MCF\Sms;

final class SmsMessages
{
    private function __construct()
    {
    }

    public static function verifyPhone(string $code): string
    {
        return __('Your phone verification code is: :code', [
            'code' => $code,
        ]);
    }

    public static function resetPassword(string $code): string
    {
        return __('Your password reset code is: :code', [
            'code' => $code,
        ]);
    }

    public static function changePhone(string $code): string
    {
        return __('Your phone change verification code is: :code', [
            'code' => $code,
        ]);
    }

    public static function welcome(string $name): string
    {
        return __('Welcome, :name! Thank you for joining us.', [
            'name' => $name,
        ]);
    }
}
