<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\UpdatePreferenceSettings;

use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use RuntimeException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(UpdatePreferenceSettingsCommand::class)]
final readonly class UpdatePreferenceSettingsCommandHandler
{
    public function __construct(private UserRepository $userRepository) {}

    public function __invoke(UpdatePreferenceSettingsCommand $updatePreferenceSettingsCommand): Result
    {
        $user = $this->userRepository->findUserById($updatePreferenceSettingsCommand->userId);
        if ($user === null) {
            return Result::failure(new RuntimeException('User not found.'));
        }

        $user->setThemePreference($updatePreferenceSettingsCommand->themePreference);
        $user->setLanguagePreference($updatePreferenceSettingsCommand->languagePreference);

        $this->userRepository->updateUser($user);

        return Result::success([
            'user_id' => $user->getId()->get('value'),
            'theme_preference' => $user->getThemePreference(),
            'language_preference' => $user->getLanguagePreference(),
        ]);
    }
}
