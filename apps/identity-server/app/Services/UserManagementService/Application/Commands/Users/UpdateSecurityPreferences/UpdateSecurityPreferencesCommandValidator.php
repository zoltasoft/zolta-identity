<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\UpdateSecurityPreferences;

use Zolta\Cqrs\Attributes\ValidatesCommand;
use Zolta\Cqrs\Services\Result;

#[ValidatesCommand(UpdateSecurityPreferencesCommand::class)]
final class UpdateSecurityPreferencesCommandValidator
{
    public function __invoke(UpdateSecurityPreferencesCommand $updateSecurityPreferencesCommand): Result
    {
        return Result::success();
    }
}
