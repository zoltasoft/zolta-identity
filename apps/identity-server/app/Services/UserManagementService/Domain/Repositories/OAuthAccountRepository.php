<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Repositories;

use App\Services\UserManagementService\Domain\Entities\OAuthAccount;
use App\Services\UserManagementService\Domain\Entities\OAuthProvider;
use Zolta\Domain\ValueObjects\OAuthProviderId;
use Zolta\Domain\ValueObjects\UserId;

interface OAuthAccountRepository
{
    public function saveOAuthAccount(OAuthAccount $oAuthAccount): void;

    public function findOAuthAccountByToken(OAuthProvider $oAuthProvider, string $oAuthUserToken): ?OAuthAccount;

    public function deleteByUserId(UserId $userId): void;

    public function findByProviderAndProviderUserId(OAuthProviderId $oAuthProviderId, string $providerUserId): ?OAuthAccount;
}
