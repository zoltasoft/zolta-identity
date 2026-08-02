<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\UpdateSecurityPreferences;

use Zolta\Cqrs\Commands\Command;
use Zolta\Domain\ValueObjects\Email;
use Zolta\Domain\ValueObjects\UserId;

final class UpdateSecurityPreferencesCommand extends Command
{
    public function __construct(
        public readonly UserId $userId,
        public readonly bool $twoFactorEnabled,
        public readonly bool $loginAlertsEnabled,
        public readonly ?Email $backupEmail = null,
    ) {}
}
