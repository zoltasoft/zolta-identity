<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ConfigureIdentityProjectRegistration;

use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ConfigureIdentityProjectRegistration;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(ConfigureIdentityProjectRegistrationCommand::class)]
final readonly class ConfigureIdentityProjectRegistrationCommandHandler
{
    public function __construct(private ConfigureIdentityProjectRegistration $projects) {}

    public function __invoke(ConfigureIdentityProjectRegistrationCommand $command): Result
    {
        $this->projects->updateProjectRegistration(
            $command->actorUserId,
            $command->projectId->toString(),
            $command->mode->value,
            $command->roleId,
            $command->emailVerificationRequired,
        );

        return Result::success(new IdentityOperationPayload([
            'message' => 'Registration policy updated.',
        ]));
    }
}
