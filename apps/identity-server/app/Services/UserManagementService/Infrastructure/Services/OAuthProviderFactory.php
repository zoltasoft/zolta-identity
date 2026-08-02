<?php

namespace App\Services\UserManagementService\Infrastructure\Services;

use App\Services\UserManagementService\Infrastructure\Services\Socialite\GoogleProviderAdapter;

class OAuthProviderFactory
{
    public function make(string $oAuthProvider): OAuthProviderInterface
    {
        return match ($oAuthProvider) {
            'google' => new GoogleProviderAdapter,
            // SocialProvider::LinkedIn => new LinkedInProviderAdapter(),
            default => throw new \InvalidArgumentException("No adapter for {$oAuthProvider}"),
        };
    }
}
