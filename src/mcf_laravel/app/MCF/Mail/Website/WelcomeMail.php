<?php

declare(strict_types=1);

namespace App\MCF\Mail\Website;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class WelcomeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $name,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Welcome'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'MCF::Mail.Website.Views.welcome',
            with: [
                'name' => $this->name,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
