<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Aggregates;

use App\Services\UserManagementService\Domain\Entities\OAuthAccount;
use App\Services\UserManagementService\Domain\Events\UserRegisteredEvent;
use DateTimeImmutable;
use Zolta\Domain\Aggregates\AggregateRoot;
use Zolta\Domain\ValueObjects\Credit;
use Zolta\Domain\ValueObjects\Email;
use Zolta\Domain\ValueObjects\OAuthProviderId;
use Zolta\Domain\ValueObjects\Password;
use Zolta\Domain\ValueObjects\Terms;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Domain\ValueObjects\Username;

/**
 * User aggregate root. Creation and reconstitution should go through factories.
 */
final class User extends AggregateRoot
{
    private readonly DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    /**
     * Private constructor: use factory methods to create/restore.
     */
    private function __construct(
        private readonly UserId $userId,
        private Email $email,
        private Username $username,
        private Password $password,
        private Credit $credit,
        private readonly Terms $terms,
        private ?string $profilePicture = null,
        private ?string $themePreference = null,
        private ?string $languagePreference = null,
        private bool $twoFactorEnabled = false,
        private bool $loginAlertsEnabled = true,
        private ?Email $backupEmail = null,
        /** @var OAuthAccount[] */
        private array $oAuthAccounts = [],
        private ?string $verificationCode = null,
        private ?DateTimeImmutable $verificationCodeExpiresAt = null,
        private ?string $passwordResetToken = null,
        private ?DateTimeImmutable $passwordResetExpiresAt = null,
        private bool $locked = false,
        private ?DateTimeImmutable $lockExpiry = null,
        private readonly bool $temporary = false,
        private readonly ?DateTimeImmutable $demoExpiresAt = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable;
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable;
    }

    /**
     * Create a new User aggregate (intended to be called by UserFactory).
     * Records UserRegisteredEvent.
     */
    public static function create(
        UserId $userId,
        Email $email,
        Username $username,
        Password $password,
        Credit $credit,
        Terms $terms,
        ?string $profilePicture = null,
        ?string $themePreference = null,
        ?string $languagePreference = null,
        bool $twoFactorEnabled = false,
        bool $loginAlertsEnabled = true,
        ?Email $backupEmail = null,
        bool $temporary = false,
        ?DateTimeImmutable $demoExpiresAt = null,
    ): self {
        $now = new DateTimeImmutable;

        $user = new self(
            $userId,
            $email,
            $username,
            $password,
            $credit,
            $terms,
            $profilePicture,
            $themePreference,
            $languagePreference,
            $twoFactorEnabled,
            $loginAlertsEnabled,
            $backupEmail,
            [],
            null,
            null,
            null,
            null,
            false,
            null,
            $temporary,
            $demoExpiresAt,
            $now,
            $now
        );

        $user->recordThat(new UserRegisteredEvent($user->getId()));
        $user->generateEmailVerificationCode();

        return $user;
    }

    /**
     * Restore (reconstitute) user aggregate from persistence.
     * Factories / repositories should call this with data fetched from DB.
     */
    public static function restore(
        UserId $userId,
        Email $email,
        Username $username,
        Password $password,
        Credit $credit,
        Terms $terms,
        ?string $profilePicture = null,
        ?string $themePreference = null,
        ?string $languagePreference = null,
        bool $twoFactorEnabled = false,
        bool $loginAlertsEnabled = true,
        ?Email $backupEmail = null,
        array $oAuthAccounts = [],
        ?string $verificationCode = null,
        ?DateTimeImmutable $verificationCodeExpiresAt = null,
        ?string $passwordResetToken = null,
        ?DateTimeImmutable $passwordResetExpiresAt = null,
        bool $locked = false,
        ?DateTimeImmutable $lockExpiry = null,
        bool $temporary = false,
        ?DateTimeImmutable $demoExpiresAt = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ): self {
        return new self(
            $userId,
            $email,
            $username,
            $password,
            $credit,
            $terms,
            $profilePicture,
            $themePreference,
            $languagePreference,
            $twoFactorEnabled,
            $loginAlertsEnabled,
            $backupEmail,
            $oAuthAccounts,
            $verificationCode,
            $verificationCodeExpiresAt,
            $passwordResetToken,
            $passwordResetExpiresAt,
            $locked,
            $lockExpiry,
            $temporary,
            $demoExpiresAt,
            $createdAt,
            $updatedAt
        );
    }

    // -------------------------
    // Domain behavior
    // -------------------------

    public function changeUsername(Username $username): void
    {
        if ($this->username->equals($username)) {
            return;
        }

        $this->username = $username;
        $this->touch();
    }

    public function setProfilePicture(?string $profilePicture): void
    {
        if ($this->profilePicture === $profilePicture) {
            return;
        }

        $this->profilePicture = $profilePicture;
        $this->touch();
    }

    public function setThemePreference(?string $preference): void
    {
        if ($this->themePreference === $preference) {
            return;
        }

        $this->themePreference = $preference;
        $this->touch();
    }

    public function setLanguagePreference(?string $preference): void
    {
        if ($this->languagePreference === $preference) {
            return;
        }

        $this->languagePreference = $preference;
        $this->touch();
    }

    public function isTwoFactorEnabled(): bool
    {
        return $this->twoFactorEnabled;
    }

    public function hasLoginAlertsEnabled(): bool
    {
        return $this->loginAlertsEnabled;
    }

    public function getBackupEmail(): ?Email
    {
        return $this->backupEmail;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function getThemePreference(): ?string
    {
        return $this->themePreference;
    }

    public function getLanguagePreference(): ?string
    {
        return $this->languagePreference;
    }

    public function updateSecurityPreferences(bool $twoFactorEnabled, bool $loginAlertsEnabled, ?Email $backupEmail = null): void
    {
        $hasChanges = false;

        if ($this->twoFactorEnabled !== $twoFactorEnabled) {
            $this->twoFactorEnabled = $twoFactorEnabled;
            $hasChanges = true;
        }

        if ($this->loginAlertsEnabled !== $loginAlertsEnabled) {
            $this->loginAlertsEnabled = $loginAlertsEnabled;
            $hasChanges = true;
        }

        $currentBackup = $this->backupEmail?->get('address');
        $nextBackup = $backupEmail?->get('address');
        if ($currentBackup !== $nextBackup) {
            $this->backupEmail = $backupEmail;
            $hasChanges = true;
        }

        if ($hasChanges) {
            $this->touch();
        }
    }

    /**
     * Change the user's email (requires verification).
     */
    public function changeEmail(Email $newEmail): void
    {
        if ($this->email->equals($newEmail)) {
            return;
        }

        $this->email = $newEmail;
        $this->generateEmailVerificationCode();
        $this->touch();
    }

    /**
     * Add credit to the user's balance.
     */
    public function addCredit(Credit $credit): void
    {
        $current = (float) ($this->credit->get('amount') ?? 0.0);
        $toAdd = (float) ($credit->get('amount') ?? 0.0);
        $currency = $this->credit->get('currency') ?? $credit->get('currency') ?? 'USD';

        $this->credit = new Credit($current + $toAdd, $currency);
        $this->touch();
    }

    public function getVerificationCode(): ?string
    {
        return $this->verificationCode;
    }

    /**
     * Add an OAuth account, ignoring duplicates.
     */
    public function addOAuthAccount(OAuthAccount $oAuthAccount): void
    {
        foreach ($this->oAuthAccounts as $existing) {
            if ($existing->isSame($oAuthAccount)) {
                return;
            }
        }

        $this->oAuthAccounts[] = $oAuthAccount;
        $this->touch();
    }

    /**
     * Get OAuth account by provider id (returns first match or null).
     */
    public function getOAuthAccountByProviderId(OAuthProviderId $oAuthProviderId): ?OAuthAccount
    {
        foreach ($this->oAuthAccounts as $oAuthAccount) {
            if ($oAuthAccount->getOAuthProviderId()->equals($oAuthProviderId)) {
                return $oAuthAccount;
            }
        }

        return null;
    }

    // -------------------------
    // Email verification
    // -------------------------

    public function generateEmailVerificationCode(): void
    {
        if ($this->email->isVerified()) {
            return;
        }

        $this->verificationCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->verificationCodeExpiresAt = new DateTimeImmutable('+1 day');
        $this->touch();
    }

    public function verifyEmail(string $code): bool
    {
        if ($this->verificationCode === null || $this->verificationCodeExpiresAt === null) {
            return false;
        }

        if ($this->verificationCode !== $code) {
            return false;
        }

        if ($this->verificationCodeExpiresAt < new DateTimeImmutable) {
            return false;
        }

        $this->email = Email::resolve([
            'address' => $this->email->get('address'),
            'verifiedAt' => new DateTimeImmutable,
        ]);
        $this->verificationCode = null;
        $this->verificationCodeExpiresAt = null;
        $this->touch();

        return true;
    }

    // -------------------------
    // Password management
    // -------------------------

    // public function verifyPassword(string $password): bool
    // {
    //     return $this->password->verify($password);
    // }

    public function initiatePasswordReset(string $token, DateTimeImmutable $expiry): void
    {
        $this->passwordResetToken = $token;
        $this->passwordResetExpiresAt = $expiry;
        $this->touch();
    }

    public function resetPassword(Password $newPassword, string $token): void
    {
        if (! $this->isPasswordResetTokenValid($token)) {
            throw new \DomainException('Invalid or expired reset token');
        }

        $this->password = $newPassword;
        $this->passwordResetToken = null;
        $this->passwordResetExpiresAt = null;
        $this->touch();
    }

    private function isPasswordResetTokenValid(string $token): bool
    {
        return $this->passwordResetToken !== null
            && $this->passwordResetExpiresAt !== null
            && $this->passwordResetToken === $token
            && $this->passwordResetExpiresAt > new DateTimeImmutable;
    }

    // -------------------------
    // Locking
    // -------------------------

    public function isLocked(): bool
    {
        return $this->locked && ($this->lockExpiry === null || $this->lockExpiry > new DateTimeImmutable);
    }

    public function lock(DateTimeImmutable $expiry): void
    {
        $this->locked = true;
        $this->lockExpiry = $expiry;
        $this->touch();
    }

    public function unlock(): void
    {
        $this->locked = false;
        $this->lockExpiry = null;
        $this->touch();
    }

    // -------------------------
    // Getters
    // -------------------------

    public function getId(): UserId
    {
        return $this->userId;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getUsername(): Username
    {
        return $this->username;
    }

    public function getPassword(): Password
    {
        return $this->password;
    }

    public function getCredit(): Credit
    {
        return $this->credit;
    }

    public function getTerms(): Terms
    {
        return $this->terms;
    }

    /**
     * @return OAuthAccount[]
     */
    public function getOAuthAccounts(): array
    {
        return $this->oAuthAccounts;
    }

    public function getVerificationCodeExpiresAt(): ?DateTimeImmutable
    {
        return $this->verificationCodeExpiresAt;
    }

    public function getLockExpiry(): ?DateTimeImmutable
    {
        return $this->lockExpiry;
    }

    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }

    public function getPasswordResetExpiry(): ?DateTimeImmutable
    {
        return $this->passwordResetExpiresAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isTemporary(): bool
    {
        return $this->temporary;
    }

    public function getDemoExpiresAt(): ?DateTimeImmutable
    {
        return $this->demoExpiresAt;
    }

    // -------------------------
    // Internal helpers
    // -------------------------

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable;
    }
}
