<?php

namespace App\Services\UserManagementService\Infrastructure\Mappers;

use App\Services\UserManagementService\Domain\Aggregates\User as DomainUser;
use App\Services\UserManagementService\Domain\Factories\OAuthAccountFactory;
use App\Services\UserManagementService\Domain\Factories\PermissionFactory;
use App\Services\UserManagementService\Domain\Factories\RoleFactory;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User as EloquentUser;
use Illuminate\Support\Carbon;
use Zolta\Cqrs\Repositories\Mapper\RepositoryMapper;
use Zolta\Domain\ValueObjects\Credit;
use Zolta\Domain\ValueObjects\Email;
use Zolta\Domain\ValueObjects\Password;
use Zolta\Domain\ValueObjects\Terms;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Domain\ValueObjects\Username;

class UserMapper implements RepositoryMapper
{
    /**
     * Map a domain entity to a persistence model or array.
     */
    public static function toPersistence(object $entity): object|array
    {
        // Accept DomainUser only for now
        if ($entity instanceof DomainUser) {
            return self::toEloquent($entity);
        }
        throw new \InvalidArgumentException('Unsupported entity type for toPersistence in UserMapper');
    }

    /**
     * Map an iterable of EloquentUser models to DomainUser aggregates (generator).
     * Usage: foreach (UserMapper::toDomainIterable($models) as $user) { ... }
     *
     * @param  iterable<EloquentUser>  $models
     * @return \Generator<int, DomainUser>
     */
    public static function toDomainIterable(iterable $models): \Generator
    {
        foreach ($models as $model) {
            yield self::toDomain($model);
        }
    }

    /**
     * Map an iterable of DomainUser aggregates to EloquentUser models (generator).
     * Usage: foreach (UserMapper::toEloquentIterable($users) as $model) { ... }
     *
     * @param  iterable<DomainUser>  $users
     * @return \Generator<int, EloquentUser>
     */
    public static function toEloquentIterable(iterable $users): \Generator
    {
        foreach ($users as $user) {
            yield self::toEloquent($user);
        }
    }

    // ... (rest of the UserMapper methods: toDomain, toEloquent, toUpdatedEloquent, etc.)
    /**
     * Maps an Eloquent model to a domain aggregate by reconstituting it.
     *
     * This method will attempt to map related role, permissions and social accounts
     * if they are loaded on the Eloquent model. The repository should eager-load
     * relations (role, permissions, socialAccounts) before calling this mapper.
     */
    public static function toDomain(object $model): object
    {
        // if (!($model instanceof EloquentUser)) {
        //     throw new \InvalidArgumentException('Expected EloquentUser in toDomain');
        // }
        $permissionFactory = new PermissionFactory;
        $oauthFactory = new OAuthAccountFactory;

        // ---- Role & role-permissions (restore aggregate via RoleFactory) ----
        $role = null;
        if ($model->relationLoaded('role') && $model->role !== null) {
            $roleFactory = new RoleFactory;
            $roleRow = $model->role->toArray();
            $rolePermissionRows = $model->role->relationLoaded('permissions') ? $model->role->permissions->toArray() : [];
            $role = $roleFactory->restoreFromRow($roleRow, $rolePermissionRows, $permissionFactory, []);
        }

        // ---- User-level permissions (permission_user pivot) ----
        $permissions = [];
        if ($model->relationLoaded('permissions')) {
            foreach ($model->permissions as $permModel) {
                $permissions[] = $permissionFactory->restoreFromRow($permModel->toArray());
            }
        }

        // ---- OAuth / social accounts ----
        $oauthAccounts = [];
        if ($model->relationLoaded('socialAccounts')) {
            foreach ($model->socialAccounts as $orow) {
                $oauthAccounts[] = $oauthFactory->restoreFromRow($orow->toArray());
            }
        }

        // ---- Build the domain User ----
        $id = new UserId((string) $model->id);
        $email = Email::resolve([
            'address' => (string) $model->email,
            'verifiedAt' => $model->email_verified_at ? new \DateTimeImmutable((string) $model->email_verified_at) : null,
        ]);
        $username = new Username((string) $model->username);
        $password = new Password((string) $model->password);

        $credit = Credit::resolve(['amount' => $model->credit ?? 0.0, 'currency' => 'USD']);
        $terms = Terms::from($model->terms ?? 'declined');
        $profilePicture = $model->profile_picture ?? null;
        $themePreference = $model->theme_preference ?? null;
        $languagePreference = $model->language_preference ?? null;
        $twoFactorEnabled = (bool) ($model->two_factor_enabled ?? false);
        $loginAlertsEnabled = (bool) ($model->login_alerts_enabled ?? true);
        $backupEmail = ! empty($model->backup_email) ? Email::resolve(['address' => $model->backup_email]) : null;

        $verificationCode = $model->verification_code ?? null;
        $verificationExpires = ! empty($model->verification_expires_at) ? new \DateTimeImmutable((string) $model->verification_expires_at) : null;
        $pwResetToken = $model->password_reset_token ?? null;
        $pwResetExpires = ! empty($model->password_reset_expires_at) ? new \DateTimeImmutable((string) $model->password_reset_expires_at) : null;
        $locked = (bool) ($model->locked ?? false);
        $lockExpiry = ! empty($model->lock_expiry) ? new \DateTimeImmutable((string) $model->lock_expiry) : null;
        $createdAt = ! empty($model->created_at) ? new \DateTimeImmutable((string) $model->created_at) : null;
        $updatedAt = ! empty($model->updated_at) ? new \DateTimeImmutable((string) $model->updated_at) : null;
        $temporary = (bool) ($model->is_temporary ?? false);
        $demoExpiresAt = ! empty($model->demo_expires_at) ? new \DateTimeImmutable((string) $model->demo_expires_at) : null;

        $domainUser = DomainUser::restore(
            $id,
            $email,
            $username,
            $password,
            $credit,
            $role,
            $terms,
            $profilePicture,
            $themePreference,
            $languagePreference,
            $twoFactorEnabled,
            $loginAlertsEnabled,
            $backupEmail,
            $oauthAccounts,
            $permissions,
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

        return $domainUser;
    }

    /**
     * Convert a DomainUser into a fresh EloquentUser instance (not persisted).
     * Returns a new EloquentUser with attributes filled — you can call save() on it.
     *
     * Note: This creates an Eloquent model populated with attributes. Relations
     * (role/permissions/social_accounts) should be persisted separately.
     */
    public static function toEloquent(DomainUser $user): EloquentUser
    {
        $attrs = [
            'id' => (string) $user->getId()->get('value'),
            'email' => $user->getEmail()->get('address'),
            'email_verified_at' => $user->getEmail()->get('verifiedAt')?->format('Y-m-d H:i:s'),
            'username' => (string) $user->getUsername()->get('username'),
            'password' => (string) $user->getPassword()->get('hash'),
            'role_id' => (string) $user->getRole()->getId()->get('value'),
            'credit' => $user->getCredit()->get('amount'),
            'terms' => $user->getTerms(),
            'profile_picture' => $user->getProfilePicture(),
            'theme_preference' => $user->getThemePreference(),
            'language_preference' => $user->getLanguagePreference(),
            'two_factor_enabled' => $user->isTwoFactorEnabled(),
            'login_alerts_enabled' => $user->hasLoginAlertsEnabled(),
            'backup_email' => $user->getBackupEmail()?->get('address'),
            'verification_code' => $user->getVerificationCode(),
            'verification_expires_at' => $user->getVerificationCodeExpiresAt()?->format('Y-m-d H:i:s'),
            'is_temporary' => $user->isTemporary(),
            'demo_expires_at' => $user->getDemoExpiresAt()?->format('Y-m-d H:i:s'),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        return new EloquentUser($attrs);
    }

    /**
     * Update an existing Eloquent model with values from the domain aggregate.
     * This mutates and returns the provided $model (you still need to call save()).
     */
    public static function toUpdatedEloquent(EloquentUser $model, DomainUser $user): EloquentUser
    {
        $model->email = $user->getEmail()->get('address');
        $model->email_verified_at = $user->getEmail()->get('verifiedAt')?->format('Y-m-d H:i:s');
        $model->username = (string) $user->getUsername()->get('username');
        $model->password = (string) $user->getPassword()->get('hash');
        $model->role_id = (string) ($user->getRoleId()?->get('value') ?? $model->role_id);
        $model->credit = $user->getCredit()->get('amount');
        $model->terms = $user->getTerms()->value;
        $model->profile_picture = $user->getProfilePicture();
        $model->theme_preference = $user->getThemePreference();
        $model->language_preference = $user->getLanguagePreference();
        $model->two_factor_enabled = $user->isTwoFactorEnabled();
        $model->login_alerts_enabled = $user->hasLoginAlertsEnabled();
        $model->backup_email = $user->getBackupEmail()?->get('address');
        $model->verification_code = $user->getVerificationCode();
        $model->verification_expires_at = $user->getVerificationCodeExpiresAt()?->format('Y-m-d H:i:s');
        $model->is_temporary = $user->isTemporary();
        $model->demo_expires_at = $user->getDemoExpiresAt()?->format('Y-m-d H:i:s');

        // updated_at should be handled by Eloquent automatically on save,
        // but we set it explicitly if domain has a value.
        $model->updated_at = Carbon::parse($user->getUpdatedAt());

        return $model;
    }
}
