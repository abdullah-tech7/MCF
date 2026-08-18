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

    public static function send(
        string $to,
        Mailable $mail,
    ): void {
        Mail::to($to)->queue($mail);
    }

    public static function queue(
        string $to,
        Mailable $mail,
    ): void {
        Mail::to($to)->queue($mail);
    }

    public static function later(
        int $delay,
        string $to,
        Mailable $mail,
    ): void {
        Mail::to($to)->later($delay, $mail);
    }
}
