<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Factories;

use App\Services\UserManagementService\Domain\Entities\OAuthAccount;
use DateTimeImmutable;
use Throwable;
use Zolta\Domain\ValueObjects\AccessToken;
use Zolta\Domain\ValueObjects\AvatarUrl;
use Zolta\Domain\ValueObjects\OAuthAccountId;
use Zolta\Domain\ValueObjects\OAuthProviderId;
use Zolta\Domain\ValueObjects\RefreshToken;
use Zolta\Domain\ValueObjects\UserId;

final class OAuthAccountFactory
{
    /**
     * Create a new OAuthAccount (for insert).
     */
    public function create(
        UserId $userId,
        OAuthProviderId $oAuthProviderId,
        string $providerUserId,
        AccessToken $accessToken,
        RefreshToken $refreshToken,
        AvatarUrl $avatarUrl,
        ?string $providerName = null
    ): OAuthAccount {
        $now = new DateTimeImmutable;

        return OAuthAccount::restore(
            new OAuthAccountId,
            $userId,
            $oAuthProviderId,
            $providerUserId,
            $accessToken,
            $refreshToken,
            $avatarUrl,
            $providerName,
            $now,
            $now
        );
    }

    public function sync(
        ?OAuthAccount $oAuthAccount,
        UserId $userId,
        OAuthProviderId $oAuthProviderId,
        string $providerUserId,
        AccessToken $accessToken,
        RefreshToken $refreshToken,
        AvatarUrl $avatarUrl,
        ?string $providerName = null
    ): OAuthAccount {
        $id = $oAuthAccount?->getId() ?? new OAuthAccountId;
        $createdAt = $oAuthAccount?->getCreatedAt() ?? new DateTimeImmutable;
        $updatedAt = new DateTimeImmutable;

        return OAuthAccount::restore(
            $id,
            $userId,
            $oAuthProviderId,
            $providerUserId,
            $accessToken,
            $refreshToken,
            $avatarUrl,
            $providerName,
            $createdAt,
            $updatedAt
        );
    }

    /**
     * Restore from a DB row (array) coming from social_accounts table.
     *
     * Expected keys: id, user_id, social_provider_id, social_provider_user_id,
     * access_token, refresh_token, avatar_url, created_at, updated_at
     */
    public function restoreFromRow(array $row): OAuthAccount
    {
        $oAuthAccountId = new OAuthAccountId((string) ($row['id'] ?? $row['uuid'] ?? ''));
        $userId = new UserId((string) $row['user_id']);
        $oAuthProviderId = new OAuthProviderId((string) $row['social_provider_id']);
        $providerUserId = (string) ($row['social_provider_user_id'] ?? $row['social_provider_user_id'] ?? '');

        $accessToken = $this->makeAccessToken(
            $row['access_token'] ?? null,
            $row['access_token_expires_at'] ?? null
        );

        $refreshToken = $this->makeRefreshToken(
            $row['refresh_token'] ?? null,
            $row['refresh_token_expires_at'] ?? null
        );

        $avatarUrl = $this->makeAvatarUrl(
            $row['avatar_url'] ?? null,
            (string) ($row['user_id'] ?? '')
        );

        $createdAt = isset($row['created_at']) ? new DateTimeImmutable((string) $row['created_at']) : new DateTimeImmutable;
        $updatedAt = isset($row['updated_at']) ? new DateTimeImmutable((string) $row['updated_at']) : $createdAt;

        $providerName = $this->extractProviderName($row);

        return OAuthAccount::restore(
            $oAuthAccountId,
            $userId,
            $oAuthProviderId,
            $providerUserId,
            $accessToken,
            $refreshToken,
            $avatarUrl,
            $providerName,
            $createdAt,
            $updatedAt
        );
    }

    private function makeAccessToken(?string $token, mixed $expiresAt): AccessToken
    {
        if (empty($token)) {
            return AccessToken::generate();
        }

        $expires = $this->parseDateTime($expiresAt) ?? new DateTimeImmutable;

        return new AccessToken((string) $token, $expires);
    }

    private function makeRefreshToken(?string $token, mixed $expiresAt): RefreshToken
    {
        if (empty($token)) {
            return RefreshToken::generate();
        }

        $expires = $this->parseDateTime($expiresAt) ?? new DateTimeImmutable;

        return new RefreshToken((string) $token, $expires);
    }

    private function makeAvatarUrl(?string $value, string $userId): AvatarUrl
    {
        $value = trim((string) $value);
        if ($value !== '') {
            return new AvatarUrl($value);
        }

        $fallback = sprintf(
            'https://www.gravatar.com/avatar/%s',
            md5($userId !== '' ? $userId : (string) random_int(PHP_INT_MIN, PHP_INT_MAX))
        );

        return new AvatarUrl($fallback);
    }

    private function parseDateTime(mixed $value): ?DateTimeImmutable
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        try {
            return new DateTimeImmutable((string) $value);
        } catch (Throwable) {
            return null;
        }
    }

    private function extractProviderName(array $row): ?string
    {
        $candidate = $row['provider'] ?? $row['social_provider'] ?? $row['provider_name'] ?? null;

        if (is_array($candidate)) {
            $value = $candidate['social_provider'] ?? $candidate['name'] ?? $candidate['provider'] ?? null;

            return is_string($value) && $value !== '' ? $value : null;
        }

        if (is_object($candidate) && method_exists($candidate, 'toArray')) {
            $candidateArray = $candidate->toArray();
            $value = $candidateArray['social_provider'] ?? $candidateArray['name'] ?? $candidateArray['provider'] ?? null;

            return is_string($value) && $value !== '' ? $value : null;
        }

        if (is_string($candidate) && $candidate !== '') {
            return $candidate;
        }

        return null;
    }
}
