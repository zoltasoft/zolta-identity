<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityVerification;

use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\VerifyIdentityEmail;
use App\Services\UserManagementService\Application\Enums\Identity\IdentityVerificationOperation;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(ExecuteIdentityVerificationCommand::class)]
final readonly class ExecuteIdentityVerificationCommandHandler
{
    public function __construct(private VerifyIdentityEmail $verification) {}

    public function __invoke(ExecuteIdentityVerificationCommand $command): Result
    {
        $result = match ($command->operation) {
            IdentityVerificationOperation::Resend => $this->verification
                ->resendEmailVerification($command->actorUserId, $command->accessToken),
            IdentityVerificationOperation::Verify => $this->verify($command),
        };

        return Result::success(new IdentityOperationPayload($result));
    }

    /** @return array{message: string} */
    private function verify(ExecuteIdentityVerificationCommand $command): array
    {
        $this->verification->verifyEmail(
            $command->actorUserId,
            $command->accessToken,
            (string) $command->input['code'],
        );

        return ['message' => 'Email address verified.'];
    }
}
