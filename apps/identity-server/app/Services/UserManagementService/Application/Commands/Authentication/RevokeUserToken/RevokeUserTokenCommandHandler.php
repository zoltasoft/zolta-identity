<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\RevokeUserToken;

use App\Services\UserManagementService\Application\Contracts\AuthenticationServiceInterface;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(RevokeUserTokenCommand::class)]
final readonly class RevokeUserTokenCommandHandler
{
    public function __construct(private AuthenticationServiceInterface $authenticationService) {}

    public function __invoke(RevokeUserTokenCommand $revokeUserTokenCommand): Result
    {
        $this->authenticationService->revokeUserToken($revokeUserTokenCommand->tokenId);

        return Result::success();
    }
}
