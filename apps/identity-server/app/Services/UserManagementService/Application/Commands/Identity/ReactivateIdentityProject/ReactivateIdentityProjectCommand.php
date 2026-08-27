<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ReactivateIdentityProject;

use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use Zolta\Cqrs\Commands\Command;

final class ReactivateIdentityProjectCommand extends Command
{
    public function __construct(
        public readonly string $actorUserId,
        public readonly IdentityProjectId $projectId,
    ) {}
}
