<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Identity\ExecuteIdentityProjectAccess;

use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityProjectAccess;
use App\Services\UserManagementService\Application\Enums\Identity\IdentityProjectAccessOperation;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(ExecuteIdentityProjectAccessCommand::class)]
final readonly class ExecuteIdentityProjectAccessCommandHandler
{
    public function __construct(private ManageIdentityProjectAccess $access) {}

    public function __invoke(ExecuteIdentityProjectAccessCommand $command): Result
    {
        $result = match ($command->operation) {
            IdentityProjectAccessOperation::CreateRole => $this->access->createRole(
                $command->actorUserId,
                $command->projectId,
                $command->input,
            ),
            IdentityProjectAccessOperation::DeleteRole => $this->deleteRole($command),
            IdentityProjectAccessOperation::CreatePermission => $this->access->createPermission(
                $command->actorUserId,
                $command->projectId,
                $command->input,
            ),
            IdentityProjectAccessOperation::DeletePermission => $this->deletePermission($command),
            IdentityProjectAccessOperation::SetRolePermissions => $this->setRolePermissions($command),
            IdentityProjectAccessOperation::Invite => $this->access->invite(
                $command->actorUserId,
                $command->projectId,
                (string) $command->input['hosted_application_id'],
                (string) $command->input['email'],
                (bool) ($command->input['is_admin'] ?? false),
            ),
            IdentityProjectAccessOperation::SetMembershipAccess => $this->setMembershipAccess($command),
            IdentityProjectAccessOperation::RemoveMembership => $this->removeMembership($command),
        };

        return Result::success(new IdentityOperationPayload($result));
    }

    /** @return array{message: string} */
    private function deleteRole(ExecuteIdentityProjectAccessCommand $command): array
    {
        $this->access->deleteRole(
            $command->actorUserId,
            $command->projectId,
            (string) $command->input['role'],
            (string) $command->input['confirmation'],
        );

        return ['message' => 'Role deleted.'];
    }

    /** @return array{message: string} */
    private function deletePermission(ExecuteIdentityProjectAccessCommand $command): array
    {
        $this->access->deletePermission(
            $command->actorUserId,
            $command->projectId,
            (string) $command->input['permission'],
            (string) $command->input['confirmation'],
        );

        return ['message' => 'Permission deleted.'];
    }

    /** @return array{message: string} */
    private function setRolePermissions(ExecuteIdentityProjectAccessCommand $command): array
    {
        $this->access->setRolePermissions(
            $command->actorUserId,
            $command->projectId,
            (string) $command->input['role'],
            (array) $command->input['permission_ids'],
        );

        return ['message' => 'Role permissions updated.'];
    }

    /** @return array{message: string} */
    private function setMembershipAccess(ExecuteIdentityProjectAccessCommand $command): array
    {
        $this->access->setMembershipAccess(
            $command->actorUserId,
            $command->projectId,
            (string) $command->input['membership'],
            (array) $command->input['role_ids'],
            (array) $command->input['permission_ids'],
            (bool) $command->input['is_admin'],
            (string) $command->input['status'],
        );

        return ['message' => 'Membership access updated.'];
    }

    /** @return array{message: string} */
    private function removeMembership(ExecuteIdentityProjectAccessCommand $command): array
    {
        $this->access->removeMembership(
            $command->actorUserId,
            $command->projectId,
            (string) $command->input['membership'],
        );

        return ['message' => 'Membership removed.'];
    }
}
