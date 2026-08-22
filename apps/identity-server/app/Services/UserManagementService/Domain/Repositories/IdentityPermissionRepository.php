<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\IdentityPermission;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityClientId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityPermissionId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;

interface IdentityPermissionRepository
{
    public function findForProject(
        IdentityProjectId $projectId,
        IdentityPermissionId $permissionId,
    ): ?IdentityPermission;

    public function findByKey(IdentityProjectId $projectId, string $key): ?IdentityPermission;

    /** @return list<IdentityPermission> */
    public function findForManifestClient(
        IdentityProjectId $projectId,
        IdentityClientId $clientId,
    ): array;

    public function save(IdentityPermission $permission): void;

    public function delete(IdentityPermission $permission): void;
}
