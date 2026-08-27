<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Identity;

use App\Services\UserManagementService\Application\Commands\Identity\UploadIdentityHostedApplicationLogo\UploadIdentityHostedApplicationLogoCommand;
use App\Services\UserManagementService\Application\DTOs\Input\UploadIdentityHostedApplicationLogoDTO;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class UploadIdentityHostedApplicationLogoService
{
    public function __construct(private ApplicationService $applicationService) {}

    /** @return array<string, mixed> */
    public function __invoke(UploadIdentityHostedApplicationLogoDTO $dto): array
    {
        ['result' => $result] = $this->applicationService->runAndCapture(
            UploadIdentityHostedApplicationLogoCommand::class,
            [
                'actorUserId' => $dto->actorUserId,
                'projectId' => $dto->projectId,
                'applicationId' => $dto->applicationId,
                'logo' => $dto->logo,
            ],
        )->getOrFail();

        return $result;
    }
}
