<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Factories;

use App\Services\UserManagementService\Domain\Entities\OAuthProvider;
use Zolta\Domain\ValueObjects\OAuthProvider as OAuthProviderEnum;
use Zolta\Domain\ValueObjects\OAuthProviderId;

final class OAuthProviderFactory
{
    public function create(OAuthProviderEnum $oAuthProviderEnum): OAuthProvider
    {
        return OAuthProvider::create($oAuthProviderEnum);
    }

    public function restore(OAuthProviderId $oAuthProviderId, OAuthProviderEnum $oAuthProviderEnum): OAuthProvider
    {
        return OAuthProvider::restore($oAuthProviderId, $oAuthProviderEnum);
    }
}
