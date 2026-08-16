<?php

declare(strict_types=1);

namespace App\MCF\Sms\Provider;

// use Vonage\Client;
// use Vonage\Client\Credentials\Basic;
// use Vonage\SMS\Message\SMS;

final class VonageSmsService implements SmsProviderContract
{
    /*
     * private readonly Client $client;
     */

    public function __construct()
    {
        /*
         * $credentials = new Basic(
         *     config('services.vonage.key'),
         *     config('services.vonage.secret'),
         * );
         *
         * $this->client = new Client($credentials);
         */
    }

    public function send(
        string $to,
        string $message,
    ): void {
        /*
         * $this->client->sms()->send(
         *     new SMS(
         *         $to,
         *         config('services.vonage.from'),
         *         $message,
         *     ),
         * );
         */
    }
}
