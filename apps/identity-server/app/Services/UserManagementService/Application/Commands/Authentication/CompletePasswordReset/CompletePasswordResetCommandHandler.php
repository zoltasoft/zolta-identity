<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\CompletePasswordReset;

use App\Services\UserManagementService\Application\Contracts\PasswordRecoveryServiceInterface;
use App\Services\UserManagementService\Domain\Events\PasswordChanged;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;
use Zolta\Domain\ValueObjects\UserId;

#[HandlesCommand(CompletePasswordResetCommand::class)]
final readonly class CompletePasswordResetCommandHandler
{
    public function __construct(
        private PasswordRecoveryServiceInterface $passwordRecovery,
    ) {}

    public function __invoke(CompletePasswordResetCommand $completePasswordResetCommand): Result
    {
        $userId = $this->passwordRecovery->resetPassword(
            email: $completePasswordResetCommand->email,
            token: $completePasswordResetCommand->token,
            password: $completePasswordResetCommand->password
        );

        return Result::successWithEvents([
            new PasswordChanged(new UserId($userId), 'reset'),
        ]);
    }
}
