<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\UploadIdentityHostedApplicationLogo;

use App\Services\UserManagementService\Application\DTOs\External\UploadedAsset;
use Zolta\Cqrs\Commands\Command;

final class UploadIdentityHostedApplicationLogoCommand extends Command
{
    public function __construct(
        public readonly string $actorUserId,
        public readonly string $projectId,
        public readonly string $applicationId,
        public readonly UploadedAsset $logo,
    ) {}
}
