<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Users;

use App\Services\UserManagementService\Application\Commands\Users\UpdateSecurityPreferences\UpdateSecurityPreferencesCommand;
use App\Services\UserManagementService\Application\DTOs\Input\UpdateSecurityPreferencesDTO;
use App\Services\UserManagementService\Application\DTOs\Output\SecurityPreferencesResponseDTO;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Domain\ValueObjects\Email;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class UpdateSecurityPreferencesService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(UpdateSecurityPreferencesDTO $updateSecurityPreferencesDTO): SecurityPreferencesResponseDTO
    {
        $payload = $this->applicationService->runAndCapture(UpdateSecurityPreferencesCommand::class, [
            'userId' => new UserId($updateSecurityPreferencesDTO->userId),
            'twoFactorEnabled' => $updateSecurityPreferencesDTO->twoFactorEnabled,
            'loginAlertsEnabled' => $updateSecurityPreferencesDTO->loginAlertsEnabled,
            'backupEmail' => $updateSecurityPreferencesDTO->backupEmail ? new Email($updateSecurityPreferencesDTO->backupEmail) : null,
        ])->getOrFail();

        return SecurityPreferencesResponseDTO::fromArray($payload);
    }
}
