<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Authentication;

use App\Services\UserManagementService\Application\DTOs\External\AuthenticatedUser;
use App\Services\UserManagementService\Application\DTOs\Input\RefreshDTO;
use App\Services\UserManagementService\Application\DTOs\Output\AuthenticatedUserResponseDTO;
use App\Services\UserManagementService\Application\Queries\Users\GetUserById\GetUserByIdQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class AuthenticatedUserService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(RefreshDTO $refreshDTO): AuthenticatedUserResponseDTO
    {
        $this->applicationService->capture(['input' => [
            'userId' => $refreshDTO->userId,
        ]]);

        ['user' => $user] = $this->applicationService->cqrs()->run(GetUserByIdQuery::class, [
            'id' => $refreshDTO->userId,
        ])->getOrFail(static fn (): RuntimeException => new RuntimeException('Authenticated user not found.'));

        return new AuthenticatedUserResponseDTO(
            user: AuthenticatedUser::fromDomain($user)
        );
    }
}
