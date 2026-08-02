<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\CreateIdentityProject;

use App\Services\UserManagementService\Application\Contracts\Identity\Projects\CreateIdentityProject;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(CreateIdentityProjectCommand::class)]
final readonly class CreateIdentityProjectCommandHandler
{
    public function __construct(private CreateIdentityProject $projects) {}

    public function __invoke(CreateIdentityProjectCommand $command): Result
    {
        return Result::success(new IdentityOperationPayload(
            $this->projects->createProject($command->actorUserId, [
                'name' => $command->name,
                'slug' => $command->slug,
                'description' => $command->description,
            ]),
        ));
    }
}
