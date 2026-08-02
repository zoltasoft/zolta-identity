<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services\Identity;

use App\Services\UserManagementService\Domain\Aggregates\IdentityPermission as DomainIdentityPermission;
use App\Services\UserManagementService\Domain\Repositories\IdentityMembershipRepository;
use App\Services\UserManagementService\Domain\Repositories\IdentityPermissionRepository;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityClientId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectPermission;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityProjectPermissionRepository;
use Illuminate\Support\Facades\DB;

final readonly class IdentityPermissionManifestSynchronizer
{
    public function __construct(
        private EloquentIdentityProjectPermissionRepository $permissions,
        private IdentityPermissionRepository $permissionAggregates,
        private IdentityMembershipRepository $membershipAggregates,
        private IdentityPayloadFactory $payloads,
    ) {}

    /**
     * @param  list<array{key: string, name?: string, description?: string}>  $manifest
     * @return list<array<string, mixed>>
     */
    public function sync(string $projectId, string $clientId, array $manifest): array
    {
        $projectIdentity = IdentityProjectId::fromString($projectId);
        $clientIdentity = IdentityClientId::fromString($clientId);
        $keys = collect($manifest)->pluck('key')->unique()->values()->all();

        DB::transaction(function () use ($projectIdentity, $clientIdentity, $manifest, $keys): void {
            foreach ($this->permissionAggregates->findForManifestClient(
                $projectIdentity,
                $clientIdentity,
            ) as $permission) {
                if (! in_array($permission->key(), $keys, true)) {
                    $permission->markStale();
                    $this->permissionAggregates->save($permission);
                }
            }

            foreach ($manifest as $item) {
                $key = (string) $item['key'];
                $name = isset($item['name']) ? (string) $item['name'] : $key;
                $description = isset($item['description'])
                    ? (string) $item['description']
                    : null;
                $permission = $this->permissionAggregates->findByKey(
                    $projectIdentity,
                    $key,
                );

                if ($permission instanceof DomainIdentityPermission) {
                    $permission->synchronizeFromManifest($clientIdentity, $name, $description);
                } else {
                    $permission = DomainIdentityPermission::createFromManifest(
                        $projectIdentity,
                        $clientIdentity,
                        $key,
                        $name,
                        $description,
                    );
                }

                $this->permissionAggregates->save($permission);
            }
        });

        $this->membershipAggregates->incrementAuthorizationVersionForProject($projectIdentity);

        return $this->permissions
            ->listForProject($projectId, sort: ['key'])
            ->map(fn (IdentityProjectPermission $permission) => $this->payloads->permission($permission))
            ->all();
    }
}
