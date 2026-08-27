<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ReactivateIdentityProject;

use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityProjectSuspension;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(ReactivateIdentityProjectCommand::class)]
final readonly class ReactivateIdentityProjectCommandHandler
{
    public function __construct(private ManageIdentityProjectSuspension $projects) {}

    public function __invoke(ReactivateIdentityProjectCommand $command): Result
    {
        return Result::success(new IdentityOperationPayload($this->projects->reactivateProject(
            $command->actorUserId,
            $command->projectId->toString(),
        )));
    }
}
