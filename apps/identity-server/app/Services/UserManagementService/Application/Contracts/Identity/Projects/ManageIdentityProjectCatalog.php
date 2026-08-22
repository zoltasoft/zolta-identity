<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Projects;

interface ManageIdentityProjectCatalog
{
    /** @return array<string, mixed> */
    public function catalog(string $actorUserId): array;

    /** @param array<string, mixed> $attributes @return array<string, mixed> */
    public function createCatalogPermission(string $actorUserId, array $attributes): array;

    /** @param array<string, mixed> $attributes @return array<string, mixed> */
    public function createCatalogRole(string $actorUserId, array $attributes): array;

    /** @param list<string> $permissionIds @param list<string> $roleIds @return array<string, mixed> */
    public function importCatalogItems(string $actorUserId, string $projectId, array $permissionIds, array $roleIds): array;

    /** @return array<string, mixed> */
    public function publishProjectPermission(string $actorUserId, string $projectId, string $permissionId): array;

    /** @return array<string, mixed> */
    public function publishProjectRole(string $actorUserId, string $projectId, string $roleId): array;
}
