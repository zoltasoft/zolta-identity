<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\ChangePassword;

use App\Services\UserManagementService\Application\Contracts\AccountSecurityServiceInterface;
use App\Services\UserManagementService\Domain\Events\PasswordChanged;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(ChangePasswordCommand::class)]
final readonly class ChangePasswordCommandHandler
{
    public function __construct(private AccountSecurityServiceInterface $accountSecurity) {}

    public function __invoke(ChangePasswordCommand $command): Result
    {
        $this->accountSecurity->changePassword(
            $command->userId,
            $command->currentPassword,
            $command->newPassword,
            $command->currentTokenId
        );

        return Result::successWithEvents([
            new PasswordChanged($command->userId, 'changed'),
        ]);
    }
}
