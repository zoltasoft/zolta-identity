<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\SyncIdentityClientManifest;

use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\SyncIdentityClientManifest;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(SyncIdentityClientManifestCommand::class)]
final readonly class SyncIdentityClientManifestCommandHandler
{
    public function __construct(private SyncIdentityClientManifest $manifests) {}

    public function __invoke(SyncIdentityClientManifestCommand $command): Result
    {
        return Result::success(new IdentityOperationPayload(
            $this->manifests->syncOwnPermissionManifest(
                $command->clientId,
                $command->clientSecret,
                $command->permissions,
            ),
        ));
    }
}
