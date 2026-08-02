<?php

namespace App\Services\UserManagementService\Infrastructure\Services\Socialite;

use App\Services\UserManagementService\Infrastructure\DTOs\OAuthUser;
use App\Services\UserManagementService\Infrastructure\Services\OAuthProviderInterface;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User;

class GoogleProviderAdapter implements OAuthProviderInterface
{
    public function fetchOAuthUser(string $accessToken): OAuthUser
    {
        /** @var GoogleProvider */
        $driver = Socialite::driver('google');

        /** @var User $providerUser */
        $providerUser = $driver->userFromToken($accessToken);

        return new OAuthUser(
            $providerUser->getId(),
            $providerUser->getName(),
            $providerUser->getEmail(),
            $accessToken,
            $providerUser->refreshToken,
            $providerUser->getAvatar(),
        );
    }
}
