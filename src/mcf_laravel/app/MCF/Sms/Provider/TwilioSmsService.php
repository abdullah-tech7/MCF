<?php

declare(strict_types=1);

namespace App\MCF\Sms\Provider;

// use Twilio\Rest\Client;

final class TwilioSmsService implements SmsProviderContract
{
    /*
     * private readonly Client $client;
     */

    public function __construct()
    {
        /*
         * $this->client = new Client(
         *     config('services.twilio.sid'),
         *     config('services.twilio.token'),
         * );
         */
    }

    public function send(
        string $to,
        string $message,
    ): void {
        /*
         * $this->client->messages->create(
         *     $to,
         *     [
         *         'from' => config('services.twilio.from'),
         *         'body' => $message,
         *     ],
         * );
         */
    }
}
