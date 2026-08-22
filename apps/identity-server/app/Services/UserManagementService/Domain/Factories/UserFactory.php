<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Factories;

use App\Services\UserManagementService\Domain\Aggregates\User;
use DateTimeImmutable;
use Zolta\Domain\ValueObjects\Credit;
use Zolta\Domain\ValueObjects\Email;
use Zolta\Domain\ValueObjects\Password;
use Zolta\Domain\ValueObjects\Terms;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Domain\ValueObjects\Username;

/**
 * Factory responsible for creating and reconstituting User aggregates.
 */
final readonly class UserFactory
{
    public function __construct(
        private OAuthAccountFactory $oauthAccountFactory,
    ) {}

    /**
     * Create a new registered user.
     *
     * $data expects keys: email, username, password (plain), termsAccepted (bool), credit (optional)
     */
    public function create(array $data): User
    {
        $userId = new UserId;
        $username = Username::resolve(['username' => $data['username']]);
        // The infrastructure model owns password hashing through Laravel's
        // configured `hashed` cast. Reconstituted users still use fromHashed().
        $password = new Password((string) $data['password']);
        $email = Email::resolve([
            'address' => $data['email'],
            'verifiedAt' => $data['email_verified_at'] ?? null,
        ]);
        $terms = $data['terms'];

        $creditAmount = (float) ($data['credit'] ?? 0.0);
        $credit = new Credit($creditAmount, 'USD');

        $profilePicture = $data['profile_picture'] ?? $data['avatar_url'] ?? null;
        $themePreference = $data['theme_preference'] ?? 'system';
        $languagePreference = $data['language_preference'] ?? 'en-US';
        $twoFactorEnabled = (bool) ($data['two_factor_enabled'] ?? false);
        $loginAlertsEnabled = array_key_exists('login_alerts_enabled', $data)
            ? (bool) $data['login_alerts_enabled']
            : true;
        $backupEmail = ! empty($data['backup_email']) ? Email::resolve(['address' => $data['backup_email']]) : null;

        $user = User::create(
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
            (bool) ($data['is_temporary'] ?? false),
            ! empty($data['demo_expires_at'])
                ? new DateTimeImmutable((string) $data['demo_expires_at'])
                : null,
        );

        return $user;
    }

    /**
     * Reconstitute a User aggregate from persistence.
     */
    public function restore(
        array $userRow,
        // array $roleRow,
        array $oauthRows = []
    ): User {
        $userId = new UserId((string) $userRow['id']);
        $email = Email::resolve([
            'address' => $userRow['email'],
            'verifiedAt' => $userRow['email_verified_at'] ?? null,
        ]);
        $username = new Username((string) $userRow['username']);
        $password = Password::fromHashed((string) $userRow['password']);
        $credit = new Credit((float) ($userRow['credit'] ?? 0.0), 'USD');
        $terms = Terms::from((string) ($userRow['terms'] ?? 'declined'));
        $profilePicture = $userRow['profile_picture'] ?? null;
        $themePreference = $userRow['theme_preference'] ?? 'system';
        $languagePreference = $userRow['language_preference'] ?? 'en-US';
        $oauthAccounts = [];
        foreach ($oauthRows as $oauthRow) {
            $oauthAccounts[] = $this->oauthAccountFactory->restoreFromRow($oauthRow);
        }

        $verificationCode = $userRow['verification_code'] ?? null;
        $verificationExpires = ! empty($userRow['verification_expires_at'])
            ? new DateTimeImmutable((string) $userRow['verification_expires_at'])
            : null;
        $pwResetToken = $userRow['password_reset_token'] ?? null;
        $pwResetExpires = ! empty($userRow['password_reset_expires_at'])
            ? new DateTimeImmutable((string) $userRow['password_reset_expires_at'])
            : null;
        $locked = (bool) ($userRow['locked'] ?? false);
        $lockExpiry = ! empty($userRow['lock_expiry']) ? new DateTimeImmutable((string) $userRow['lock_expiry']) : null;
        $createdAt = ! empty($userRow['created_at']) ? new DateTimeImmutable((string) $userRow['created_at']) : null;
        $updatedAt = ! empty($userRow['updated_at']) ? new DateTimeImmutable((string) $userRow['updated_at']) : null;
        $temporary = (bool) ($userRow['is_temporary'] ?? false);
        $demoExpiresAt = ! empty($userRow['demo_expires_at'])
            ? new DateTimeImmutable((string) $userRow['demo_expires_at'])
            : null;

        $twoFactorEnabled = (bool) ($userRow['two_factor_enabled'] ?? false);
        $loginAlertsEnabled = array_key_exists('login_alerts_enabled', $userRow)
            ? (bool) $userRow['login_alerts_enabled']
            : true;
        $backupEmail = ! empty($userRow['backup_email'])
            ? Email::resolve(['address' => $userRow['backup_email']])
            : null;

        return User::restore(
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
            $oauthAccounts,
            $verificationCode,
            $verificationExpires,
            $pwResetToken,
            $pwResetExpires,
            $locked,
            $lockExpiry,
            $temporary,
            $demoExpiresAt,
            $createdAt,
            $updatedAt
        );
    }
}
