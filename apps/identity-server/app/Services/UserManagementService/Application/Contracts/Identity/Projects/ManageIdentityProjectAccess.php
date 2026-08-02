<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Projects;

interface ManageIdentityProjectAccess
{
    /** @param array<string, mixed> $attributes @return array<string, mixed> */
    public function createRole(string $actorUserId, string $projectId, array $attributes): array;

    /** @param array<string, mixed> $attributes @return array<string, mixed> */
    public function createPermission(string $actorUserId, string $projectId, array $attributes): array;

    /** @param list<string> $permissionIds */
    public function setRolePermissions(string $actorUserId, string $projectId, string $roleId, array $permissionIds): void;

    /** @return array<string, mixed> */
    public function invite(string $actorUserId, string $projectId, string $email, bool $isAdmin): array;

    /** @param list<string> $roleIds @param list<string> $permissionIds */
    public function setMembershipAccess(
        string $actorUserId,
        string $projectId,
        string $membershipId,
        array $roleIds,
        array $permissionIds,
        bool $isAdmin,
        string $status,
    ): void;

    public function removeMembership(string $actorUserId, string $projectId, string $membershipId): void;
}
