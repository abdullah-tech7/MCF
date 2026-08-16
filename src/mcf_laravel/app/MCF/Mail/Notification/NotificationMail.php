<?php

declare(strict_types=1);

namespace App\MCF\Mail\Notification;

use App\MCF\Notification\NotificationData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class NotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly NotificationData $notification,
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notification->title
                ?? __('You have a new notification.'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'MCF::Mail.Notification.Views.notification',
            with: [
                'notification' => $this->notification,
            ],
        );
    }
}
