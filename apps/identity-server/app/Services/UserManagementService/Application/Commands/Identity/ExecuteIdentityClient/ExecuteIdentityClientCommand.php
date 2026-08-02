<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityClient;

use App\Services\UserManagementService\Application\Enums\Identity\IdentityClientOperation;
use Zolta\Cqrs\Commands\Command;

final class ExecuteIdentityClientCommand extends Command
{
    /** @param array<string, mixed> $input */
    public function __construct(
        public readonly IdentityClientOperation $operation,
        public readonly array $input,
        public readonly string $actorUserId,
        public readonly string $projectId,
    ) {}
}
