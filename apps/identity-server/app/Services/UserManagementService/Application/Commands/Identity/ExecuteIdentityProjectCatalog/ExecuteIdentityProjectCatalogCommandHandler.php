<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityProjectCatalog;

use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityProjectCatalog;
use App\Services\UserManagementService\Application\Enums\Identity\IdentityProjectCatalogOperation;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(ExecuteIdentityProjectCatalogCommand::class)]
final readonly class ExecuteIdentityProjectCatalogCommandHandler
{
    public function __construct(private ManageIdentityProjectCatalog $catalog) {}

    public function __invoke(ExecuteIdentityProjectCatalogCommand $command): Result
    {
        $input = $command->input;
        $result = match ($command->operation) {
            IdentityProjectCatalogOperation::Index => $this->catalog->catalog($command->actorUserId),
            IdentityProjectCatalogOperation::CreatePermission => $this->catalog->createCatalogPermission($command->actorUserId, $input),
            IdentityProjectCatalogOperation::CreateRole => $this->catalog->createCatalogRole($command->actorUserId, $input),
            IdentityProjectCatalogOperation::Import => $this->catalog->importCatalogItems($command->actorUserId, (string) $input['project'], (array) ($input['permission_ids'] ?? []), (array) ($input['role_ids'] ?? [])),
            IdentityProjectCatalogOperation::PublishPermission => $this->catalog->publishProjectPermission($command->actorUserId, (string) $input['project'], (string) $input['permission']),
            IdentityProjectCatalogOperation::PublishRole => $this->catalog->publishProjectRole($command->actorUserId, (string) $input['project'], (string) $input['role']),
        };

        return Result::success(new IdentityOperationPayload($result));
    }
}
