<?php

declare(strict_types=1);

namespace App\MCF\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

final class McfMail
{
    private function __construct()
    {
    }

    /*
    |--------------------------------------------------------------------------
    | Send
    |--------------------------------------------------------------------------
    |
    | Queued email delivery.
    |
    */

    public static function send(
        string $to,
        Mailable $mail,
    ): void {
        Mail::to($to)->queue($mail);
    }

    /*
    |--------------------------------------------------------------------------
    | Direct
    |--------------------------------------------------------------------------
    |
    | Sends the email immediately without using the queue.
    |
    */

    public static function direct(
        string $to,
        Mailable $mail,
    ): void {
        Mail::to($to)->send($mail);
    }

    /*
    |--------------------------------------------------------------------------
    | Later
    |--------------------------------------------------------------------------
    |
    | Queued email delivery with a delay in seconds.
    |
    */

    public static function later(
        int $delay,
        string $to,
        Mailable $mail,
    ): void {
        Mail::to($to)->later($delay, $mail);
    }
}
