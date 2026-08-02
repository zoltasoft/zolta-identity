<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Users;

use App\Services\UserManagementService\Application\Commands\Users\DeleteUserByEmail\DeleteUserByEmailCommand;
use App\Services\UserManagementService\Application\DTOs\Input\DeleteUserByEmailDTO;
use App\Services\UserManagementService\Application\DTOs\Output\DeleteUserByEmailResponseDTO;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class DeleteUserByEmailService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(DeleteUserByEmailDTO $deleteUserByEmailDTO): DeleteUserByEmailResponseDTO
    {

        $result = $this->applicationService->runAndCapture(DeleteUserByEmailCommand::class, [
            'email' => $deleteUserByEmailDTO->email,
        ])->getOrFail();

        return new DeleteUserByEmailResponseDTO(message: $result['message']);
    }
}
