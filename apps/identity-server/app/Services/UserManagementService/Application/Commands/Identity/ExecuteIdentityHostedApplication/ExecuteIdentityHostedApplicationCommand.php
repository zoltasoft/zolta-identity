<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityHostedApplication;

use App\Services\UserManagementService\Application\Enums\Identity\IdentityHostedApplicationOperation;
use Zolta\Cqrs\Commands\Command;

final class ExecuteIdentityHostedApplicationCommand extends Command
{
    /** @param array<string, mixed> $input */
    public function __construct(
        public readonly IdentityHostedApplicationOperation $operation,
        public readonly array $input,
        public readonly string $actorUserId,
        public readonly string $projectId,
    ) {}
}
