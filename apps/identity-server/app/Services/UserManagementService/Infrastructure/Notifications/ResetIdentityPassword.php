<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ResetIdentityPassword extends Notification
{
    use Queueable;

    public function __construct(private readonly string $token) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Reset your identity password')
            ->line('A password reset was requested for your identity account.');
        $resetUrl = (string) config('identity.password_reset_url');
        if ($resetUrl !== '') {
            $separator = str_contains($resetUrl, '?') ? '&' : '?';
            $mail->action('Reset password', $resetUrl.$separator.http_build_query([
                'email' => $notifiable->email,
                'token' => $this->token,
            ]));
        } else {
            $mail->line('Use this token in the application that requested the reset:')
                ->line($this->token);
        }

        return $mail->line('If you did not request this reset, ignore this message.');
    }
}
