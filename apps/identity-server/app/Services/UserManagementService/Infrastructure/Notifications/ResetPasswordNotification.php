<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $baseUrl = rtrim((string) config('app.frontend_url'), '/');
        $query = http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Reset your InterviewLike password')
            ->greeting('Reset your password')
            ->line('We received a password reset request for your InterviewLike account.')
            ->action('Choose a new password', "{$baseUrl}/auth/reset-password?{$query}")
            ->line('This link expires in '.config('auth.passwords.users.expire').' minutes.')
            ->line('If you did not request this change, you can ignore this email.');
    }
}
