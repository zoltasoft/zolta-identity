<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityWebhook;

use App\Services\UserManagementService\Application\Enums\Identity\IdentityWebhookOperation;
use Zolta\Cqrs\Commands\Command;

final class ExecuteIdentityWebhookCommand extends Command
{
    /** @param array<string, mixed> $input */
    public function __construct(
        public readonly IdentityWebhookOperation $operation,
        public readonly array $input,
        public readonly string $actorUserId,
        public readonly string $projectId,
    ) {}
}
