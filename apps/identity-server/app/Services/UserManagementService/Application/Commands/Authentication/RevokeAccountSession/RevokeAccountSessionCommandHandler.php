<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\RevokeAccountSession;

use App\Services\UserManagementService\Application\Contracts\AccountSecurityServiceInterface;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(RevokeAccountSessionCommand::class)]
final readonly class RevokeAccountSessionCommandHandler
{
    public function __construct(private AccountSecurityServiceInterface $accountSecurity) {}

    public function __invoke(RevokeAccountSessionCommand $command): Result
    {
        $this->accountSecurity->revokeSession($command->userId, $command->tokenId);

        return Result::success();
    }
}
