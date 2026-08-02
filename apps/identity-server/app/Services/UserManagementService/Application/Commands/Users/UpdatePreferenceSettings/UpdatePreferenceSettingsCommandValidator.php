<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\UpdatePreferenceSettings;

use Zolta\Cqrs\Attributes\ValidatesCommand;
use Zolta\Cqrs\Services\Result;

#[ValidatesCommand(UpdatePreferenceSettingsCommand::class)]
final class UpdatePreferenceSettingsCommandValidator
{
    private const ALLOWED_THEMES = ['light', 'dark', 'system'];

    public function __invoke(UpdatePreferenceSettingsCommand $updatePreferenceSettingsCommand): Result
    {
        if (! in_array($updatePreferenceSettingsCommand->themePreference, self::ALLOWED_THEMES, true)) {
            return Result::failure(new \InvalidArgumentException('Invalid theme preference.'));
        }

        return Result::success();
    }
}
