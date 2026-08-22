<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use App\Services\UserManagementService\Application\DTOs\External\UploadedAsset;
use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

final class UploadIdentityHostedApplicationLogoDTO extends InputDTO
{
    public function __construct(
        #[FromRequest('actor_user_id')]
        public readonly string $actorUserId,
        #[FromRequest('project_id')]
        public readonly string $projectId,
        #[FromRequest('application_id')]
        public readonly string $applicationId,
        #[FromRequest('logo')]
        public readonly UploadedAsset $logo,
    ) {}
}
