<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services;

use App\Services\UserManagementService\Application\Contracts\OAuthGateway;
use App\Services\UserManagementService\Application\DTOs\External\OAuthUser;
use InvalidArgumentException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User;

final class SocialiteOAuthGateway implements OAuthGateway
{
    public function fetchUser(string $provider, string $accessToken): OAuthUser
    {
        if ($provider !== 'google') {
            throw new InvalidArgumentException("No OAuth adapter is configured for {$provider}.");
        }

        /** @var GoogleProvider $driver */
        $driver = Socialite::driver($provider);

        /** @var User $remoteUser */
        $remoteUser = $driver->userFromToken($accessToken);

        return new OAuthUser(
            providerUserId: $remoteUser->getId(),
            name: $remoteUser->getName() ?? '',
            email: $remoteUser->getEmail() ?? '',
            accessToken: $accessToken,
            refreshToken: $remoteUser->refreshToken,
            avatarUrl: $remoteUser->getAvatar(),
        );
    }
}
