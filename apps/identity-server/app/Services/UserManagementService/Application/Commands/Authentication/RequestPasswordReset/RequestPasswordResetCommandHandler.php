<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\RequestPasswordReset;

use App\Services\UserManagementService\Application\Contracts\PasswordRecoveryServiceInterface;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(RequestPasswordResetCommand::class)]
final readonly class RequestPasswordResetCommandHandler
{
    public function __construct(private PasswordRecoveryServiceInterface $passwordRecovery) {}

    public function __invoke(RequestPasswordResetCommand $command): Result
    {
        $this->passwordRecovery->requestResetLink($command->email);

        return Result::success();
    }
}
