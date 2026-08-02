<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\CreateIdentityProject;

use Zolta\Cqrs\Commands\Command;

final class CreateIdentityProjectCommand extends Command
{
    public function __construct(
        public readonly string $actorUserId,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description = null,
    ) {}
}
