<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityVerification;

use App\Services\UserManagementService\Application\Enums\Identity\IdentityVerificationOperation;
use Zolta\Cqrs\Commands\Command;

final class ExecuteIdentityVerificationCommand extends Command
{
    /** @param array<string, mixed> $input */
    public function __construct(
        public readonly IdentityVerificationOperation $operation,
        public readonly array $input,
        public readonly string $actorUserId,
        public readonly string $accessToken,
    ) {}
}
