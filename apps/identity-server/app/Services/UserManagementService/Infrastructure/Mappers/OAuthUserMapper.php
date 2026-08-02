<?php

namespace App\Services\UserManagementService\Infrastructure\Mappers;

use App\Services\UserManagementService\Application\DTOs\External\OAuthUser as ExternalOAuthUser;
use App\Services\UserManagementService\Infrastructure\DTOs\OAuthUser;

final class OAuthUserMapper
{
    /**
     * Maps an infrastructure SocialiteUserDTO to an application OAuthUserDTO.
     */
    public static function toApplication(OAuthUser $dto): ExternalOAuthUser
    {
        return new ExternalOAuthUser(
            providerUserId: $dto->providerUserId,
            name: $dto->name,
            email: $dto->email,
            accessToken: $dto->accessToken,
            refreshToken: $dto->refreshToken,
            avatarUrl: $dto->avatarUrl
        );
    }
}
