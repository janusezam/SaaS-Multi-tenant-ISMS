<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantRegistrationSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $adminName,
        public string $requesterName,
        public string $requesterEmail,
        public string $requesterRole,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New tenant user registration pending approval',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant-registration-submitted',
            with: [
                'adminName' => $this->adminName,
                'requesterName' => $this->requesterName,
                'requesterEmail' => $this->requesterEmail,
                'requesterRole' => $this->requesterRole,
            ],
        );
    }
}
