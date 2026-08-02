<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Mail\Mailable;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUser extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $companyName,
        public readonly string $mailSubject,
        public readonly string $username,
        public readonly ?string $verificationCode = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,
        );
    }

    public function content(): Content
    {
        $username = e($this->username);
        $verification = $this->verificationCode
            ? '<p>Your email verification code is <strong style="font-size: 20px; letter-spacing: 4px;">'.e($this->verificationCode).'</strong>.</p><p>The code expires in 24 hours.</p>'
            : '';

        return new Content(
            htmlString: '<h1>Welcome to InterviewLike</h1><p>Hello '.$username.',</p><p>Your job-search workspace is ready.</p>'.$verification,
        );
    }

    /**
     * @return array<int, mixed>
     */
    public function attachments(): array
    {
        return [];
    }
}
