<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Identity;

use App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityInstallation\ExecuteIdentityInstallationCommand;
use App\Services\UserManagementService\Application\DTOs\Input\IdentityOperationDTO;
use InvalidArgumentException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class ExecuteIdentityInstallationService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(IdentityOperationDTO $dto): mixed
    {
        ['result' => $result] = $this->applicationService->runAndCapture(
            ExecuteIdentityInstallationCommand::class,
            [
                'operation' => $dto->operation,
                'input' => $dto->input,
                'actorUserId' => $dto->actorUserId
                    ?? throw new InvalidArgumentException('An authenticated Identity actor is required.'),
            ],
        )->getOrFail();

        return $result;
    }
}
