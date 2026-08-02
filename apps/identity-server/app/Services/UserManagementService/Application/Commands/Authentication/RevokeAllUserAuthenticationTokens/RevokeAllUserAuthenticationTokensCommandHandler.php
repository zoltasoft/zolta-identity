<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\RevokeAllUserAuthenticationTokens;

use App\Services\UserManagementService\Application\Contracts\AuthenticationServiceInterface;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(RevokeAllUserAuthenticationTokensCommand::class)]
final readonly class RevokeAllUserAuthenticationTokensCommandHandler
{
    public function __construct(private AuthenticationServiceInterface $authenticationService) {}

    public function __invoke(RevokeAllUserAuthenticationTokensCommand $revokeAllUserAuthenticationTokensCommand): Result
    {
        $this->authenticationService->logout();

        return Result::success();
    }
}
