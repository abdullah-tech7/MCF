<?php

declare(strict_types=1);

namespace App\MCF\Sms;

use App\MCF\Sms\Provider\SmsProviderContract;
use App\MCF\Sms\Provider\TwilioSmsService;

final class McfSms
{
    private function __construct()
    {
    }

    private static function getProvider(): SmsProviderContract
    {
        return new TwilioSmsService();
    }

    public static function send(
        string $to,
        string $message,
    ): void {
        self::getProvider()->send(
            $to,
            $message,
        );
    }
}
