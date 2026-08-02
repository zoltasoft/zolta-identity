<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Users;

use App\Services\UserManagementService\Application\DTOs\Input\GetUserByEmailDTO;
use App\Services\UserManagementService\Application\DTOs\Output\GetUserByEmailResponseDTO;
use App\Services\UserManagementService\Application\Queries\Roles\GetRoleById\GetRoleByIdQuery;
use App\Services\UserManagementService\Application\Queries\Users\GetUserByEmail\GetUserByEmailQuery;
use App\Services\UserManagementService\Domain\Aggregates\Role;
use App\Services\UserManagementService\Domain\Aggregates\User;
use App\Services\UserManagementService\Domain\Exceptions\UserNotFoundException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Exceptions\Rest\NotFoundException;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class GetUserByEmailService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(GetUserByEmailDTO $getUserByEmailDTO): GetUserByEmailResponseDTO
    {
        $this->applicationService->capture(['input' => $getUserByEmailDTO->toArray()]);

        $userResult = $this->applicationService
            ->cqrs()
            ->run(GetUserByEmailQuery::class, [
                'email' => $getUserByEmailDTO->address,
                'include' => ['include' => ['permissions', 'role']], // permissions are included here to avoid an extra query in GetRoleByIdService when mapping to response DTO, since the role's permissions are needed in the response
            ])
            ->getOrFail(fn () => throw new UserNotFoundException);

        $user = $userResult['user'] ?? null;

        if (! $user instanceof User) {
            throw new NotFoundException('Unable to resolve user aggregate from query payload.');
        }

        $roleResult = $this->applicationService
            ->cqrs()
            ->run(GetRoleByIdQuery::class, [
                'id' => $user->getRoleId()->value,
                'options' => [
                    'include' => ['permissions'],
                ],
            ])
            ->getOrFail(fn () => throw new NotFoundException('Role not found for provided user.'));

        $role = $roleResult['role'] ?? null;

        if (! $role instanceof Role) {
            throw new NotFoundException('Unable to resolve role aggregate from query payload.');
        }

        // $captureLog = array_keys($this->service->getCaptured());

        return GetUserByEmailResponseDTO::fromDomain($user, $role);
    }
}
