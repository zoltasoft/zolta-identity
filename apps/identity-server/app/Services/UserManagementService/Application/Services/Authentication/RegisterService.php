<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Authentication;

use App\Services\UserManagementService\Application\Commands\Users\RegisterUser\RegisterUserCommand;
use App\Services\UserManagementService\Application\DTOs\Input\RegisterDTO;
use App\Services\UserManagementService\Application\DTOs\Output\RegisterResponseDTO;
use App\Services\UserManagementService\Application\Queries\Roles\GetRoleById\GetRoleByIdQuery;
use App\Services\UserManagementService\Application\Queries\Roles\GetRoleByName\GetRoleByNameQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Exceptions\Rest\NotFoundException;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class RegisterService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(RegisterDTO $registerDTO): RegisterResponseDTO
    {
        ['role' => $role] = $this->applicationService
            ->runAndCapture(GetRoleByNameQuery::class, ['name' => 'User'])
            ->getOrFail(fn (): NotFoundException => new NotFoundException('Default user role not found'));

        ['user' => $user] = $this->applicationService
            ->runAndCapture(RegisterUserCommand::class, [
                'email' => $registerDTO->email,
                'password' => $registerDTO->password,
                'username' => $registerDTO->username,
                'terms' => $registerDTO->terms,
                'role' => $role,
            ])
            ->getOrFail();

        $this->applicationService->dispatchEvents($user->releaseEvents());

        ['role' => $role] = $this->applicationService
            ->runAndCapture(GetRoleByIdQuery::class, [
                'id' => $user->getRoleId(),
                'options' => [
                    'include' => ['permissions'],
                ],
            ])
            ->getOrFail(fn (): RuntimeException => new RuntimeException('Failed to find the user role'));

        return RegisterResponseDTO::fromDomain(user: $user, role: $role);
    }
}
