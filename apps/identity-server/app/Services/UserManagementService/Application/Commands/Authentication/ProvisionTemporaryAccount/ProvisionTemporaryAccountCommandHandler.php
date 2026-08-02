<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\ProvisionTemporaryAccount;

use App\Services\UserManagementService\Application\Contracts\TemporaryAccountManagerInterface;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(ProvisionTemporaryAccountCommand::class)]
final readonly class ProvisionTemporaryAccountCommandHandler
{
    public function __construct(private TemporaryAccountManagerInterface $accounts) {}

    public function __invoke(ProvisionTemporaryAccountCommand $command): Result
    {
        return Result::success(['credentials' => $this->accounts->provision()]);
    }
}
