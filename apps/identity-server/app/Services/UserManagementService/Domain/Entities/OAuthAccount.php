<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Entities;

use DateTimeImmutable;
use Zolta\Domain\Entities\Interfaces\Entity;
use Zolta\Domain\ValueObjects\AccessToken;
use Zolta\Domain\ValueObjects\AvatarUrl;
use Zolta\Domain\ValueObjects\OAuthAccountId;
use Zolta\Domain\ValueObjects\OAuthProviderId;
use Zolta\Domain\ValueObjects\RefreshToken;
use Zolta\Domain\ValueObjects\UserId;

/**
 * OAuthAccount entity. Immutable-like; created/restored via factory.
 */
final readonly class OAuthAccount implements Entity
{
    private function __construct(private OAuthAccountId $oAuthAccountId, private UserId $userId, private OAuthProviderId $oAuthProviderId, private string $oAuthProviderUserId, private AccessToken $accessToken, private RefreshToken $refreshToken, private AvatarUrl $avatarUrl, private ?string $providerName, private DateTimeImmutable $createdAt, private DateTimeImmutable $updatedAt) {}

    // used by factory
    public static function restore(
        OAuthAccountId $oAuthAccountId,
        UserId $userId,
        OAuthProviderId $oAuthProviderId,
        string $providerUserId,
        AccessToken $accessToken,
        RefreshToken $refreshToken,
        AvatarUrl $avatarUrl,
        ?string $providerName,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self($oAuthAccountId, $userId, $oAuthProviderId, $providerUserId, $accessToken, $refreshToken, $avatarUrl, $providerName, $createdAt, $updatedAt);
    }

    // ---------- Behavior ----------

    public function isSame(self $other): bool
    {
        return $this->oAuthProviderId->equals($other->getOAuthProviderId())
            && $this->oAuthProviderUserId === $other->getOAuthProviderUserId();
    }

    // ---------- Getters ----------

    public function getId(): OAuthAccountId
    {
        return $this->oAuthAccountId;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getOAuthProviderId(): OAuthProviderId
    {
        return $this->oAuthProviderId;
    }

    public function getOAuthProviderUserId(): string
    {
        return $this->oAuthProviderUserId;
    }

    public function getAccessToken(): AccessToken
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): RefreshToken
    {
        return $this->refreshToken;
    }

    public function getAvatarUrl(): AvatarUrl
    {
        return $this->avatarUrl;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getProviderName(): ?string
    {
        return $this->providerName;
    }
}
