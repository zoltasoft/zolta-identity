<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ResetIdentityPassword extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly ?string $clientId = null,
        private readonly ?string $authPageSet = null,
    ) {}

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
        $resetUrl = (string) config('zolta.identity.password_reset_url');
        if ($resetUrl !== '') {
            $resetUrl = $this->withHostedAuthPageSet($resetUrl);
            $separator = str_contains($resetUrl, '?') ? '&' : '?';
            $mail->action('Reset password', $resetUrl.$separator.http_build_query(array_filter([
                'email' => $notifiable->email,
                'token' => $this->token,
                'client_id' => $this->clientId,
            ])));
        } else {
            $mail->line('Use this token in the application that requested the reset:')
                ->line($this->token);
        }

        return $mail->line('If you did not request this reset, ignore this message.');
    }

    private function withHostedAuthPageSet(string $resetUrl): string
    {
        if ($this->authPageSet === null
            || $this->authPageSet === ''
            || $this->authPageSet === 'default'
            || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $this->authPageSet) !== 1) {
            return $resetUrl;
        }

        return (string) preg_replace(
            '~\/auth\/reset-password(?=$|[?#])~',
            '/auth/'.rawurlencode($this->authPageSet).'/reset-password',
            $resetUrl,
            1,
        );
    }
}
