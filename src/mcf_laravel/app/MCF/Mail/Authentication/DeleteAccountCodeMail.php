<?php

declare(strict_types=1);

namespace App\MCF\Mail\Authentication;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class DeleteAccountCodeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $code,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Confirm Account Deletion'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'MCF::Mail.Authentication.Views.delete-account-code',
            with: [
                'code' => $this->code,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
