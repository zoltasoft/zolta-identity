<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\RemoveIdentityHostedApplicationLogo;

use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityHostedApplications;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(RemoveIdentityHostedApplicationLogoCommand::class)]
final readonly class RemoveIdentityHostedApplicationLogoCommandHandler
{
    public function __construct(private ManageIdentityHostedApplications $applications) {}

    public function __invoke(RemoveIdentityHostedApplicationLogoCommand $command): Result
    {
        $this->applications->removeHostedApplicationLogo(
            $command->actorUserId,
            $command->projectId,
            $command->applicationId,
        );

        return Result::success(new IdentityOperationPayload([
            'message' => 'Hosted application logo removed.',
        ]));
    }
}
