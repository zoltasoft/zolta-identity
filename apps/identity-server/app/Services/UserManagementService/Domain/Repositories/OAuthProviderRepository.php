<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Repositories;

use App\Services\UserManagementService\Domain\Entities\OAuthProvider;
use Zolta\Domain\ValueObjects\OAuthProvider as EnumOAuthProvider;
use Zolta\Domain\ValueObjects\OAuthProviderId;

interface OAuthProviderRepository
{
    public function findById(OAuthProviderId $oAuthProviderId): ?OAuthProvider;

    public function findByOAuthProvider(EnumOAuthProvider $enumOAuthProvider): ?OAuthProvider;

    public function saveOAuthProvider(OAuthProvider $oAuthProvider): void;

    public function deleteOAuthProvider(OAuthProvider $oAuthProvider): void;

    public function updateOAuthProvider(OAuthProvider $oAuthProvider): void;
}
