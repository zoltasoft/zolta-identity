<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ConfigureIdentityProjectEnvironment;

use App\Services\UserManagementService\Domain\Enums\IdentityProjectMode;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use Zolta\Cqrs\Commands\Command;

final class ConfigureIdentityProjectEnvironmentCommand extends Command
{
    public function __construct(
        public readonly string $actorUserId,
        public readonly IdentityProjectId $projectId,
        public readonly IdentityProjectMode $mode,
        public readonly int $sandboxTtlMinutes,
    ) {}
}
