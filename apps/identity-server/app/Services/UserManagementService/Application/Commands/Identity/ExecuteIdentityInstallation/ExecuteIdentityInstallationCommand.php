<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityInstallation;

use Zolta\Cqrs\Commands\Command;

final class ExecuteIdentityInstallationCommand extends Command
{
    /** @param array<string, mixed> $input */
    public function __construct(
        public readonly string $operation,
        public readonly array $input,
        public readonly string $actorUserId,
    ) {}
}
