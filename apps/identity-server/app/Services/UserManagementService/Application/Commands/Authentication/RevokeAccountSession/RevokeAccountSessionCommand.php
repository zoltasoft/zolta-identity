<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\RevokeAccountSession;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\UserId;

final class RevokeAccountSessionCommand extends Command
{
    public function __construct(
        public readonly UserId $userId,
        public readonly int $tokenId,
    ) {}
}
