<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Identity;

use App\Services\UserManagementService\Application\Commands\Identity\RemoveIdentityHostedApplicationLogo\RemoveIdentityHostedApplicationLogoCommand;
use App\Services\UserManagementService\Application\DTOs\Input\RemoveIdentityHostedApplicationLogoDTO;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class RemoveIdentityHostedApplicationLogoService
{
    public function __construct(private ApplicationService $applicationService) {}

    /** @return array{message: string} */
    public function __invoke(RemoveIdentityHostedApplicationLogoDTO $dto): array
    {
        ['result' => $result] = $this->applicationService->runAndCapture(
            RemoveIdentityHostedApplicationLogoCommand::class,
            [
                'actorUserId' => $dto->actorUserId,
                'projectId' => $dto->projectId,
                'applicationId' => $dto->applicationId,
            ],
        )->getOrFail();

        return $result;
    }
}
