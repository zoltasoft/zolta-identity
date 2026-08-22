<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ScheduleIdentityProjectDeletion;

use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityProjectDeletion;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(ScheduleIdentityProjectDeletionCommand::class)]
final readonly class ScheduleIdentityProjectDeletionCommandHandler
{
    public function __construct(private ManageIdentityProjectDeletion $projects) {}

    public function __invoke(ScheduleIdentityProjectDeletionCommand $command): Result
    {
        return Result::success(new IdentityOperationPayload($this->projects->scheduleProjectDeletion(
            $command->actorUserId,
            $command->projectId->toString(),
            $command->confirmation,
        )));
    }
}
