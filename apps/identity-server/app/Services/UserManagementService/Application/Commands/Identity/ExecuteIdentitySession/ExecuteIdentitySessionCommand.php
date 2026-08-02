<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentitySession;

use App\Services\UserManagementService\Application\Enums\Identity\IdentitySessionOperation;
use Zolta\Cqrs\Commands\Command;

final class ExecuteIdentitySessionCommand extends Command
{
    /** @param array<string, mixed> $input */
    public function __construct(
        public readonly IdentitySessionOperation $operation,
        public readonly array $input,
        public readonly ?string $actorUserId = null,
        public readonly ?string $accessToken = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {}
}
