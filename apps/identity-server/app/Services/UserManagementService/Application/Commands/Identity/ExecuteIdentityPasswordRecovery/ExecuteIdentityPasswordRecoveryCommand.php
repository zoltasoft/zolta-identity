<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityPasswordRecovery;

use App\Services\UserManagementService\Application\Enums\Identity\IdentityPasswordRecoveryOperation;
use Zolta\Cqrs\Commands\Command;

final class ExecuteIdentityPasswordRecoveryCommand extends Command
{
    /** @param array<string, mixed> $input */
    public function __construct(
        public readonly IdentityPasswordRecoveryOperation $operation,
        public readonly array $input,
    ) {}
}
