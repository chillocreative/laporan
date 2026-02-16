<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $roleName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Akaun Anda Telah Diluluskan',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.user-approved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
