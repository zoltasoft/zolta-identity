<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityHandoff;

use App\Services\UserManagementService\Application\Enums\Identity\IdentityHandoffOperation;
use Zolta\Cqrs\Commands\Command;

final class ExecuteIdentityHandoffCommand extends Command
{
    /** @param array<string, mixed> $input */
    public function __construct(
        public readonly IdentityHandoffOperation $operation,
        public readonly array $input,
        public readonly ?string $actorUserId = null,
        public readonly ?string $accessToken = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {}
}
