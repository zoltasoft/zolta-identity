<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityHandoff;

use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\ManageIdentityHandoffs;
use App\Services\UserManagementService\Application\Enums\Identity\IdentityHandoffOperation;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use InvalidArgumentException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(ExecuteIdentityHandoffCommand::class)]
final readonly class ExecuteIdentityHandoffCommandHandler
{
    public function __construct(private ManageIdentityHandoffs $handoffs) {}

    public function __invoke(ExecuteIdentityHandoffCommand $command): Result
    {
        $result = match ($command->operation) {
            IdentityHandoffOperation::Create => $this->handoffs->createHandoff(
                $command->actorUserId
                    ?? throw new InvalidArgumentException('An authenticated Identity actor is required.'),
                $command->accessToken
                    ?? throw new InvalidArgumentException('An Identity access token is required.'),
                $command->input,
                $command->ipAddress,
                $command->userAgent,
            ),
            IdentityHandoffOperation::Exchange => $this->handoffs->exchangeHandoff(
                $command->input,
                $command->ipAddress,
                $command->userAgent,
            ),
            IdentityHandoffOperation::AuthorizationIntentCreate => $this->handoffs->createAuthorizationIntent(
                $command->input,
                $command->ipAddress,
                $command->userAgent,
            ),
            IdentityHandoffOperation::AuthorizationIntentConsume => $this->handoffs->consumeAuthorizationIntent(
                $command->input,
                $command->ipAddress,
                $command->userAgent,
            ),
            IdentityHandoffOperation::AccountIntentCreate => $this->handoffs->createAccountPortalIntent(
                $command->actorUserId ?? throw new InvalidArgumentException('An authenticated Identity actor is required.'),
                $command->accessToken ?? throw new InvalidArgumentException('An Identity access token is required.'),
                $command->input,
                $command->ipAddress,
                $command->userAgent,
            ),
            IdentityHandoffOperation::AccountIntentConsume => $this->handoffs->consumeAccountPortalIntent(
                $command->input,
                $command->ipAddress,
                $command->userAgent,
            ),
            IdentityHandoffOperation::LogoutIntentCreate => $this->handoffs->createLogoutIntent(
                $command->input,
                $command->ipAddress,
                $command->userAgent,
            ),
            IdentityHandoffOperation::LogoutIntentConsume => $this->handoffs->consumeLogoutIntent(
                $command->input,
                $command->ipAddress,
                $command->userAgent,
            ),
        };

        return Result::success(new IdentityOperationPayload($result));
    }
}
