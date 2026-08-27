<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\UploadIdentityHostedApplicationLogo;

use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityHostedApplications;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(UploadIdentityHostedApplicationLogoCommand::class)]
final readonly class UploadIdentityHostedApplicationLogoCommandHandler
{
    public function __construct(private ManageIdentityHostedApplications $applications) {}

    public function __invoke(UploadIdentityHostedApplicationLogoCommand $command): Result
    {
        return Result::success(new IdentityOperationPayload(
            $this->applications->uploadHostedApplicationLogo(
                $command->actorUserId,
                $command->projectId,
                $command->applicationId,
                $command->logo,
            ),
        ));
    }
}
