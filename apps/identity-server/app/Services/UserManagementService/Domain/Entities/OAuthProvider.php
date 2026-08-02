<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Entities;

use Zolta\Domain\Entities\Interfaces\Entity;
use Zolta\Domain\ValueObjects\OAuthProvider as OAuthProviderEnum;
use Zolta\Domain\ValueObjects\OAuthProviderId;

final readonly class OAuthProvider implements Entity
{
    private function __construct(private OAuthProviderId $oAuthProviderId, private OAuthProviderEnum $oAuthProviderEnum) {}

    public static function create(OAuthProviderEnum $oAuthProviderEnum): self
    {
        return new self(new OAuthProviderId, $oAuthProviderEnum);
    }

    public static function restore(OAuthProviderId $oAuthProviderId, OAuthProviderEnum $oAuthProviderEnum): self
    {
        return new self($oAuthProviderId, $oAuthProviderEnum);
    }

    public function equals(self $other): bool
    {
        return $this->oAuthProviderId->equals($other->getId());
    }

    // ---------- Getters ----------

    public function getId(): OAuthProviderId
    {
        return $this->oAuthProviderId;
    }

    public function getOAuthProvider(): OAuthProviderEnum
    {
        return $this->oAuthProviderEnum;
    }
}
