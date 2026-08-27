<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\CancelIdentityProjectDeletion;

use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityProjectDeletion;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(CancelIdentityProjectDeletionCommand::class)]
final readonly class CancelIdentityProjectDeletionCommandHandler
{
    public function __construct(private ManageIdentityProjectDeletion $projects) {}

    public function __invoke(CancelIdentityProjectDeletionCommand $command): Result
    {
        return Result::success(new IdentityOperationPayload($this->projects->cancelProjectDeletion(
            $command->actorUserId,
            $command->projectId->toString(),
        )));
    }
}
