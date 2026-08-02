<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Identity;

use App\Services\UserManagementService\Application\DTOs\Input\IdentityOperationDTO;
use App\Services\UserManagementService\Application\Enums\Identity\IdentityProjectReadOperation;
use App\Services\UserManagementService\Application\Queries\Identity\ReadIdentityProjects\ReadIdentityProjectsQuery;
use InvalidArgumentException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class ReadIdentityProjectService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(IdentityOperationDTO $dto): mixed
    {
        ['result' => $result] = $this->applicationService->runAndCapture(
            ReadIdentityProjectsQuery::class,
            [
                'operation' => IdentityProjectReadOperation::from($dto->operation),
                'input' => $dto->input,
                'actorUserId' => $dto->actorUserId
                    ?? throw new InvalidArgumentException('An authenticated Identity actor is required.'),
            ],
        )->getOrFail();

        return $result;
    }
}
