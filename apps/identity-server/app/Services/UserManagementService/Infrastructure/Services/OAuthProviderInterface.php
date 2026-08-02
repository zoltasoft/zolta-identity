<?php

namespace App\Services\UserManagementService\Infrastructure\Services;

use App\Services\UserManagementService\Infrastructure\DTOs\OAuthUser;

interface OAuthProviderInterface
{
    public function fetchOAuthUser(string $accessToken): OAuthUser;
}
