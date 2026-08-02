<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\External;

use App\Services\UserManagementService\Domain\Aggregates\Permission;
use App\Services\UserManagementService\Domain\Aggregates\Role;
use App\Services\UserManagementService\Domain\Aggregates\User;

final readonly class AuthenticatedUser
{
    public function __construct(
        public string $id,
        public string $email,
        public bool $emailVerified,
        public string $username,
        public array $role,
        public array $permissions,
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
            'role' => $this->role,
            'permissions' => $this->permissions,
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

    public static function fromDomain(User $user, ?Role $role = null): self
    {
        $rolePayload = $role ? [
            'id' => $user->getRoleId()->get('value'),
            'name' => $role?->getName()->get('value') ?? null,
            'description' => $role?->getDescription()?->get('description'),
        ] : [];

        $directPermissions = array_map(
            static fn (Permission $permission): mixed => $permission->getName()->get('value'),
            $user->getPermissions()
        );

        $rolePermissions = $role
            ? array_map(
                static fn (Permission $permission): mixed => $permission->getName()->get('value'),
                $role->getPermissions()
            )
            : [];

        $permissions = array_values(array_unique(array_filter(array_merge(
            $directPermissions,
            $rolePermissions
        ))));

        return new self(
            id: $user->getId()->get('value'),
            email: $user->getEmail()->get('address'),
            emailVerified: $user->getEmail()->isVerified(),
            username: $user->getUsername()->get('username'),
            role: $rolePayload,
            permissions: $permissions,
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
