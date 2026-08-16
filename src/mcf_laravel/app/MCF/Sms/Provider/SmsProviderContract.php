<?php

declare(strict_types=1);

namespace App\MCF\Sms\Provider;

interface SmsProviderContract
{
    public function send(
        string $to,
        string $message,
    ): void;
}
