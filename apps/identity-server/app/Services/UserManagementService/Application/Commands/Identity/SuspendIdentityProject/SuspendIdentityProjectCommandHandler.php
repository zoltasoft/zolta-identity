<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\SuspendIdentityProject;

use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityProjectSuspension;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(SuspendIdentityProjectCommand::class)]
final readonly class SuspendIdentityProjectCommandHandler
{
    public function __construct(private ManageIdentityProjectSuspension $projects) {}

    public function __invoke(SuspendIdentityProjectCommand $command): Result
    {
        return Result::success(new IdentityOperationPayload($this->projects->suspendProject(
            $command->actorUserId,
            $command->projectId->toString(),
            $command->confirmation,
        )));
    }
}
