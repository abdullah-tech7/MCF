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
    | Default email delivery.
    |
    | Change the implementation here if the framework/application
    | needs to switch between queued and direct delivery.
    |
    */

    public static function send(
        string $to,
        Mailable $mail,
    ): void {
        self::queued(
            $to,
            $mail,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Queued
    |--------------------------------------------------------------------------
    */

    public static function queued(
        string $to,
        Mailable $mail,
    ): void {
        Mail::to($to)->queue($mail);
    }

    /*
    |--------------------------------------------------------------------------
    | Direct
    |--------------------------------------------------------------------------
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
    */

    public static function later(
        int $delay,
        string $to,
        Mailable $mail,
    ): void {
        Mail::to($to)->later(
            $delay,
            $mail,
        );
    }
}