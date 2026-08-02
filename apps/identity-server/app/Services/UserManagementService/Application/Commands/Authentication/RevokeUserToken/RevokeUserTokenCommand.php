<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\RevokeUserToken;

use Zolta\Cqrs\Commands\Command;

final class RevokeUserTokenCommand extends Command
{
    public function __construct(
        public readonly string $tokenId,
    ) {}
}
