<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ConfigureIdentityProjectEnvironment;

use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ConfigureIdentityProjectEnvironment;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(ConfigureIdentityProjectEnvironmentCommand::class)]
final readonly class ConfigureIdentityProjectEnvironmentCommandHandler
{
    public function __construct(private ConfigureIdentityProjectEnvironment $projects) {}

    public function __invoke(ConfigureIdentityProjectEnvironmentCommand $command): Result
    {
        $this->projects->updateProjectEnvironment(
            $command->actorUserId,
            $command->projectId->toString(),
            $command->mode->value,
            $command->sandboxTtlMinutes,
        );

        return Result::success(new IdentityOperationPayload([
            'message' => 'Project environment updated.',
        ]));
    }
}
