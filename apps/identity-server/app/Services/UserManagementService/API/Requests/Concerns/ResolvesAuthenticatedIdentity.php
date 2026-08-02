<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Requests\Concerns;

use Zolta\Http\Authorization\Identity;
use Zolta\Http\Authorization\Interfaces\UserIdentityInterface;

trait ResolvesAuthenticatedIdentity
{
    protected function hasAuthenticatedIdentity(): bool
    {
        return Identity::current() !== null;
    }

    protected function authenticatedIdentity(): ?UserIdentityInterface
    {
        return Identity::current();
    }

    protected function authenticatedUserId(): string
    {
        return (string) $this->authenticatedIdentity()?->getId();
    }

    protected function currentAccessTokenId(): ?int
    {
        $tokenId = $this->user()?->currentAccessToken()?->getKey();

        return is_numeric($tokenId) ? (int) $tokenId : null;
    }
}
