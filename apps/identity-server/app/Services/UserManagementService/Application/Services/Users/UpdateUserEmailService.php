<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Users;

use App\Services\UserManagementService\Application\Commands\Users\UpdateUserEmail\UpdateUserEmailCommand;
use App\Services\UserManagementService\Application\DTOs\Input\UpdateUserEmailDTO;
use App\Services\UserManagementService\Application\DTOs\Output\UpdateUserEmailResponseDTO;
use App\Services\UserManagementService\Domain\Aggregates\User;
use App\Services\UserManagementService\Domain\Exceptions\UserNotFoundException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class UpdateUserEmailService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(UpdateUserEmailDTO $updateUserEmailDTO): UpdateUserEmailResponseDTO
    {
        $this->applicationService->capture([
            'input' => [
                'id' => $updateUserEmailDTO->id,
                'email' => $updateUserEmailDTO->email,
            ],
        ]);

        $result = $this->applicationService
            ->runAndCapture(UpdateUserEmailCommand::class, [
                'id' => $updateUserEmailDTO->id,
                'email' => $updateUserEmailDTO->email,
            ])
            ->getOrFail();

        $user = $result['user'] ?? null;

        if (! $user instanceof User) {
            throw new UserNotFoundException;
        }

        $captureLog = array_keys($this->applicationService->getCaptured());

        return UpdateUserEmailResponseDTO::fromDomain($user, $captureLog);
    }
}
