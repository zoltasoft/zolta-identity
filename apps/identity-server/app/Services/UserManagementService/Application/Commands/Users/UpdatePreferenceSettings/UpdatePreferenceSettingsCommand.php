<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\UpdatePreferenceSettings;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\UserId;

final class UpdatePreferenceSettingsCommand extends Command
{
    public function __construct(
        public readonly UserId $userId,
        public readonly string $themePreference,
        public readonly string $languagePreference,
    ) {}
}
