<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityProjectAccess;

use App\Services\UserManagementService\Application\Enums\Identity\IdentityProjectAccessOperation;
use Zolta\Cqrs\Commands\Command;

final class ExecuteIdentityProjectAccessCommand extends Command
{
    /** @param array<string, mixed> $input */
    public function __construct(
        public readonly IdentityProjectAccessOperation $operation,
        public readonly array $input,
        public readonly string $actorUserId,
        public readonly string $projectId,
    ) {}
}
