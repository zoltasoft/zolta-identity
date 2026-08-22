<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\External;

use App\Services\UserManagementService\Domain\Aggregates\User;

final readonly class AuthenticatedUser
{
    public function __construct(
        public string $id,
        public string $email,
        public bool $emailVerified,
        public string $username,
        public bool $twoFactorEnabled = false,
        public bool $loginAlertsEnabled = true,
        public ?string $backupEmail = null,
        public ?string $profilePicture = null,
        public ?string $themePreference = null,
        public ?string $languagePreference = null,
        public bool $isTemporary = false,
        public ?string $demoExpiresAt = null,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'email_verified' => $this->emailVerified,
            'username' => $this->username,
            'two_factor_enabled' => $this->twoFactorEnabled,
            'login_alerts_enabled' => $this->loginAlertsEnabled,
            'backup_email' => $this->backupEmail,
            'profile_picture' => $this->profilePicture,
            'theme_preference' => $this->themePreference,
            'language_preference' => $this->languagePreference,
            'is_temporary' => $this->isTemporary,
            'demo_expires_at' => $this->demoExpiresAt,
        ];
    }

    public static function fromDomain(User $user): self
    {
        return new self(
            id: $user->getId()->get('value'),
            email: $user->getEmail()->get('address'),
            emailVerified: $user->getEmail()->isVerified(),
            username: $user->getUsername()->get('username'),
            twoFactorEnabled: $user->isTwoFactorEnabled(),
            loginAlertsEnabled: $user->hasLoginAlertsEnabled(),
            backupEmail: $user->getBackupEmail()?->get('address'),
            profilePicture: $user->getProfilePicture(),
            themePreference: $user->getThemePreference(),
            languagePreference: $user->getLanguagePreference(),
            isTemporary: $user->isTemporary(),
            demoExpiresAt: $user->getDemoExpiresAt()?->format(DATE_ATOM),
        );
    }
}
