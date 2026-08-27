<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts;

use App\Services\UserManagementService\Application\DTOs\External\OAuthUser;

interface OAuthGateway
{
    public function fetchUser(string $provider, string $accessToken): OAuthUser;
}
