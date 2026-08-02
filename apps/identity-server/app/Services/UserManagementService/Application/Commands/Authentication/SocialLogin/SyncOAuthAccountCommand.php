<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\SocialLogin;

use Zolta\Cqrs\Commands\Command;

final class SyncOAuthAccountCommand extends Command
{
    public function __construct(
        public readonly string $userId,
        public readonly string $providerId,
        public readonly string $providerUserId,
        public readonly string $email,
        public readonly string $accessToken,
        public readonly ?string $refreshToken = null,
        public readonly ?string $avatarUrl = null
    ) {}
}
