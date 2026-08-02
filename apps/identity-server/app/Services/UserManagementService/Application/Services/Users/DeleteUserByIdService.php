<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Users;

use App\Services\UserManagementService\Application\Commands\Users\DeleteUserById\DeleteUserByIdCommand;
use App\Services\UserManagementService\Application\DTOs\Input\DeleteUserByIdDTO;
use App\Services\UserManagementService\Application\DTOs\Output\DeleteUserByIdResponseDTO;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class DeleteUserByIdService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(DeleteUserByIdDTO $deleteUserByIdDTO): DeleteUserByIdResponseDTO
    {

        $result = $this->applicationService->runAndCapture(DeleteUserByIdCommand::class, [
            'id' => $deleteUserByIdDTO->id,
        ])->getOrFail();

        return new DeleteUserByIdResponseDTO(message: $result['message']);
    }
}
