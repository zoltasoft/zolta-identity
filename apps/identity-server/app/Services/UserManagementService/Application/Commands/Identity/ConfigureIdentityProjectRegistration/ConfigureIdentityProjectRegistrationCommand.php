<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ConfigureIdentityProjectRegistration;

use App\Services\UserManagementService\Domain\Enums\IdentityProjectRegistrationMode;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use Zolta\Cqrs\Commands\Command;

final class ConfigureIdentityProjectRegistrationCommand extends Command
{
    public function __construct(
        public readonly string $actorUserId,
        public readonly IdentityProjectId $projectId,
        public readonly IdentityProjectRegistrationMode $mode,
        public readonly ?string $roleId = null,
        public readonly bool $emailVerificationRequired = true,
    ) {}
}
