<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Authentication;

use App\Services\UserManagementService\Application\Commands\Users\RegisterUser\RegisterUserCommand;
use App\Services\UserManagementService\Application\DTOs\Input\RegisterDTO;
use App\Services\UserManagementService\Application\DTOs\Output\RegisterResponseDTO;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class RegisterService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(RegisterDTO $registerDTO): RegisterResponseDTO
    {
        ['user' => $user] = $this->applicationService
            ->runAndCapture(RegisterUserCommand::class, [
                'email' => $registerDTO->email,
                'password' => $registerDTO->password,
                'username' => $registerDTO->username,
                'terms' => $registerDTO->terms,
            ])
            ->getOrFail();

        $this->applicationService->dispatchEvents($user->releaseEvents());

        return RegisterResponseDTO::fromDomain($user);
    }
}
