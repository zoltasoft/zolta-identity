<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\UpdateSecurityPreferences;

use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use RuntimeException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Repositories\Query\QueryOptionsFactory;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(UpdateSecurityPreferencesCommand::class)]
final readonly class UpdateSecurityPreferencesCommandHandler
{
    public function __construct(private UserRepository $userRepository, private QueryOptionsFactory $queryOptionsFactory) {}

    public function __invoke(UpdateSecurityPreferencesCommand $updateSecurityPreferencesCommand): Result
    {
        $queryOptions = $this->queryOptionsFactory->make(['include' => ['role']]);
        $user = $this->userRepository->findUserById($updateSecurityPreferencesCommand->userId, $queryOptions);
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
