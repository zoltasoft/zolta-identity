<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Users;

use App\Services\UserManagementService\Application\DTOs\Input\GetUserByIdDTO;
use App\Services\UserManagementService\Application\DTOs\Output\GetUserByIdResponseDTO;
use App\Services\UserManagementService\Application\Queries\Users\GetUserById\GetUserByIdQuery;
use App\Services\UserManagementService\Domain\Exceptions\UserNotFoundException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class GetUserByIdService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(GetUserByIdDTO $getUserByIdDTO): GetUserByIdResponseDTO
    {

        ['user' => $user] = $this->applicationService->cqrs()->run(
            GetUserByIdQuery::class,
            ['id' => $getUserByIdDTO->id, 'options' => $getUserByIdDTO->options],
        )->getOrFail(fn () => throw new UserNotFoundException);

        return GetUserByIdResponseDTO::fromDomain($user);
    }
}
