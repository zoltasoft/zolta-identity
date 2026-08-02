<?php

namespace App\Services\UserManagementService\Infrastructure\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code, public string $username) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Email Address',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '<h1>Verify your InterviewLike email</h1><p>Hello '.e($this->username).',</p><p>Your verification code is <strong style="font-size: 20px; letter-spacing: 4px;">'.e($this->code).'</strong>.</p><p>The code expires in 24 hours.</p>',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
