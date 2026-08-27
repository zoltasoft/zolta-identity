<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\RemoveIdentityHostedApplicationLogo;

use Zolta\Cqrs\Commands\Command;

final class RemoveIdentityHostedApplicationLogoCommand extends Command
{
    public function __construct(
        public readonly string $actorUserId,
        public readonly string $projectId,
        public readonly string $applicationId,
    ) {}
}
