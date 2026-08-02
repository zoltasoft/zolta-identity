<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Users;

use App\Services\UserManagementService\Application\Commands\Users\UpdatePreferenceSettings\UpdatePreferenceSettingsCommand;
use App\Services\UserManagementService\Application\DTOs\Input\UpdatePreferenceSettingsDTO;
use App\Services\UserManagementService\Application\DTOs\Output\PreferenceSettingsResponseDTO;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class UpdatePreferenceSettingsService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(UpdatePreferenceSettingsDTO $updatePreferenceSettingsDTO): PreferenceSettingsResponseDTO
    {
        $payload = $this->applicationService->runAndCapture(UpdatePreferenceSettingsCommand::class, [
            'userId' => new UserId($updatePreferenceSettingsDTO->userId),
            'themePreference' => $updatePreferenceSettingsDTO->theme,
            'languagePreference' => $updatePreferenceSettingsDTO->language,
        ])->getOrFail();

        return PreferenceSettingsResponseDTO::fromArray($payload);
    }
}
