<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityAccess;

use App\Services\UserManagementService\Application\Enums\Identity\IdentityAccessOperation;
use Zolta\Cqrs\Commands\Command;

final class ExecuteIdentityAccessCommand extends Command
{
    /** @param array<string, mixed> $input */
    public function __construct(
        public readonly IdentityAccessOperation $operation,
        public readonly array $input,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {}
}
