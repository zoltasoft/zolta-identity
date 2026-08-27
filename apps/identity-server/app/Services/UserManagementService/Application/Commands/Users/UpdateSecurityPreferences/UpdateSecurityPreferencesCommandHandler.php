<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\UpdateSecurityPreferences;

use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use RuntimeException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(UpdateSecurityPreferencesCommand::class)]
final readonly class UpdateSecurityPreferencesCommandHandler
{
    public function __construct(private UserRepository $userRepository) {}

    public function __invoke(UpdateSecurityPreferencesCommand $updateSecurityPreferencesCommand): Result
    {
        $user = $this->userRepository->findUserById($updateSecurityPreferencesCommand->userId);
        if ($user === null) {
            return Result::failure(new RuntimeException('User not found'));
        }

        $user->updateSecurityPreferences(
            $updateSecurityPreferencesCommand->twoFactorEnabled,
            $updateSecurityPreferencesCommand->loginAlertsEnabled,
            $updateSecurityPreferencesCommand->backupEmail,
        );

        $this->userRepository->updateUser($user);

        return Result::success([
            'user_id' => $user->getId()->get('value'),
            'two_factor_enabled' => $user->isTwoFactorEnabled(),
            'login_alerts_enabled' => $user->hasLoginAlertsEnabled(),
            'backup_email' => $user->getBackupEmail()?->get('address'),
        ]);
    }
}
