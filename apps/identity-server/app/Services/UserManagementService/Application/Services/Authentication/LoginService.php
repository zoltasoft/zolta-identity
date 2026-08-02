<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Authentication;

use App\Services\UserManagementService\Application\Commands\Authentication\AttemptLogin\AttemptLoginCommand;
use App\Services\UserManagementService\Application\DTOs\Input\LoginDTO;
use App\Services\UserManagementService\Application\DTOs\Output\LoginResponseDTO;
use App\Services\UserManagementService\Application\Queries\Authentication\GenerateTokenFromUser\GenerateTokenFromUserQuery;
use App\Services\UserManagementService\Application\Queries\Authentication\GetAuthenticatedUser\GetAuthenticatedUserQuery;
use App\Services\UserManagementService\Application\Queries\Roles\GetRoleById\GetRoleByIdQuery;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class LoginService
{
    public function __construct(
        private ApplicationService $applicationService,
    ) {}

    public function __invoke(LoginDTO $loginDTO): LoginResponseDTO
    {
        $this->applicationService->runAndCapture(AttemptLoginCommand::class, [
            'email' => $loginDTO->email,
            'password' => $loginDTO->password,
        ])->getOrFail();
        $this->applicationService->runAndCapture(GetAuthenticatedUserQuery::class)->getOrFail();
        $this->applicationService->runAndCapture(GenerateTokenFromUserQuery::class, ['id' => map('user.id.value')])->getOrNull();
        $this->applicationService->runAndCapture(GetRoleByIdQuery::class, ['id' => map('user.roleId.value')])->getOrNull();

        return LoginResponseDTO::fromDomain(
            user: $this->applicationService->get('user'),
            accessToken: $this->applicationService->get('accessToken'),
        );
    }
}
