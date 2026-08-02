<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentitySession;

use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\ManageIdentitySessions;
use App\Services\UserManagementService\Application\Enums\Identity\IdentitySessionOperation;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use InvalidArgumentException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(ExecuteIdentitySessionCommand::class)]
final readonly class ExecuteIdentitySessionCommandHandler
{
    public function __construct(private ManageIdentitySessions $sessions) {}

    public function __invoke(ExecuteIdentitySessionCommand $command): Result
    {
        $result = match ($command->operation) {
            IdentitySessionOperation::Refresh => $this->sessions->refresh(
                $command->input,
                $command->ipAddress,
                $command->userAgent,
            ),
            IdentitySessionOperation::Logout => $this->logout($command->accessToken),
            IdentitySessionOperation::Revoke => $this->revoke($command),
        };

        return Result::success(new IdentityOperationPayload($result));
    }

    /** @return array{message: string} */
    private function logout(?string $accessToken): array
    {
        if ($accessToken !== null) {
            $this->sessions->logout($accessToken);
        }

        return ['message' => 'Session revoked.'];
    }

    /** @return array{message: string} */
    private function revoke(ExecuteIdentitySessionCommand $command): array
    {
        $actorUserId = $command->actorUserId
            ?? throw new InvalidArgumentException('An authenticated Identity actor is required.');
        $this->sessions->revokeSession(
            $actorUserId,
            (string) $command->input['session'],
        );

        return ['message' => 'Session revoked.'];
    }
}
