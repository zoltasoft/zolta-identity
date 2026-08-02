<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Authentication;

use App\Services\UserManagementService\Application\Commands\Authentication\ChangePassword\ChangePasswordCommand;
use App\Services\UserManagementService\Application\DTOs\Input\ChangePasswordDTO;
use App\Services\UserManagementService\Application\DTOs\Output\AuthenticationMessageResponseDTO;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class ChangePasswordService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(ChangePasswordDTO $dto): AuthenticationMessageResponseDTO
    {
        $this->applicationService
            ->runAndCapture(ChangePasswordCommand::class, [
                'userId' => new UserId($dto->userId),
                'currentPassword' => $dto->currentPassword,
                'newPassword' => $dto->password,
                'currentTokenId' => $dto->currentTokenId,
            ])
            ->getOrFail();

        return new AuthenticationMessageResponseDTO('Your password has been updated.');
    }
}
