<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityInstallation;

use App\Services\UserManagementService\Application\Contracts\IdentityInstallationServiceInterface;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use InvalidArgumentException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(ExecuteIdentityInstallationCommand::class)]
final readonly class ExecuteIdentityInstallationCommandHandler
{
    public function __construct(private IdentityInstallationServiceInterface $identity) {}

    public function __invoke(ExecuteIdentityInstallationCommand $command): Result
    {
        if ($command->operation !== 'installation.users.update') {
            throw new InvalidArgumentException("Unsupported Identity installation command [{$command->operation}].");
        }

        $this->identity->updateInstallationUser(
            $command->actorUserId,
            (string) $command->input['user'],
            (bool) $command->input['is_system_admin'],
            (bool) $command->input['locked'],
        );

        return Result::success(new IdentityOperationPayload(['message' => 'Installation user updated.']));
    }
}
