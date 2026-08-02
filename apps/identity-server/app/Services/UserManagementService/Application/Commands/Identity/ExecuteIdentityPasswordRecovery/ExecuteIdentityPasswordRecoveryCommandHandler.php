<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityPasswordRecovery;

use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\RecoverIdentityPassword;
use App\Services\UserManagementService\Application\Enums\Identity\IdentityPasswordRecoveryOperation;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(ExecuteIdentityPasswordRecoveryCommand::class)]
final readonly class ExecuteIdentityPasswordRecoveryCommandHandler
{
    public function __construct(private RecoverIdentityPassword $passwords) {}

    public function __invoke(ExecuteIdentityPasswordRecoveryCommand $command): Result
    {
        $result = match ($command->operation) {
            IdentityPasswordRecoveryOperation::Forgot => $this->passwords->requestPasswordReset(
                (string) $command->input['client_id'],
                (string) $command->input['client_secret'],
                (string) $command->input['email'],
            ),
            IdentityPasswordRecoveryOperation::Reset => $this->reset($command),
        };

        return Result::success(new IdentityOperationPayload($result));
    }

    /** @return array{message: string} */
    private function reset(ExecuteIdentityPasswordRecoveryCommand $command): array
    {
        $this->passwords->resetPassword(
            (string) $command->input['client_id'],
            (string) $command->input['client_secret'],
            (string) $command->input['email'],
            (string) $command->input['token'],
            (string) $command->input['password'],
        );

        return ['message' => 'Password reset completed.'];
    }
}
