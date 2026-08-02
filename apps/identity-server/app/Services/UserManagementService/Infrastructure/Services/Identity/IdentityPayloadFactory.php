<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services\Identity;

use App\Services\UserManagementService\Domain\Aggregates\IdentityClient as DomainIdentityClient;
use App\Services\UserManagementService\Domain\Aggregates\IdentityMembership as DomainIdentityMembership;
use App\Services\UserManagementService\Domain\Aggregates\IdentityPermission as DomainIdentityPermission;
use App\Services\UserManagementService\Domain\Aggregates\IdentityProject as DomainIdentityProject;
use App\Services\UserManagementService\Domain\Aggregates\IdentityRole as DomainIdentityRole;
use App\Services\UserManagementService\Domain\Aggregates\IdentityWebhook as DomainIdentityWebhook;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProject;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectClient;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectMembership;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectPermission;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectRole;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityWebhookEndpoint;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;

final class IdentityPayloadFactory
{
    /** @return array<string, mixed> */
    public function roleAggregate(DomainIdentityRole $role): array
    {
        return [
            'id' => $role->id()->toString(),
            'project_id' => $role->projectId()->toString(),
            'name' => $role->name(),
            'slug' => $role->slug(),
            'description' => $role->description(),
            'permission_ids' => array_map(
                static fn ($id): string => $id->toString(),
                $role->permissionIds(),
            ),
        ];
    }

    /** @return array<string, mixed> */
    public function permissionAggregate(DomainIdentityPermission $permission): array
    {
        return [
            'id' => $permission->id()->toString(),
            'project_id' => $permission->projectId()->toString(),
            'key' => $permission->key(),
            'name' => $permission->name(),
            'description' => $permission->description(),
            'source' => $permission->source()->value,
            'source_client_id' => $permission->sourceClientId()?->toString(),
            'status' => $permission->status()->value,
        ];
    }

    /** @return array<string, mixed> */
    public function membershipAggregate(DomainIdentityMembership $membership): array
    {
        return [
            'id' => $membership->id()->toString(),
            'project_id' => $membership->projectId()->toString(),
            'user' => ['id' => $membership->userId()->toString()],
            'status' => $membership->status()->value,
            'is_admin' => $membership->isAdministrator(),
            'authorization_version' => $membership->authorizationVersion(),
            'role_ids' => array_map(
                static fn ($id): string => $id->toString(),
                $membership->roleIds(),
            ),
            'direct_permission_ids' => array_map(
                static fn ($id): string => $id->toString(),
                $membership->permissionIds(),
            ),
        ];
    }

    /** @return array<string, mixed> */
    public function clientAggregate(DomainIdentityClient $client): array
    {
        return [
            'id' => $client->id()->toString(),
            'project_id' => $client->projectId()->toString(),
            'name' => $client->name(),
            'secret_prefix' => $client->secretPrefix(),
            'status' => $client->status()->value,
            'last_used_at' => $client->lastUsedAt()?->format(DATE_ATOM),
        ];
    }

    /** @return array<string, mixed> */
    public function webhookAggregate(DomainIdentityWebhook $webhook): array
    {
        return [
            'id' => $webhook->id()->toString(),
            'project_id' => $webhook->projectId()->toString(),
            'url' => $webhook->url(),
            'events' => $webhook->events(),
            'secret_prefix' => $webhook->secretPrefix(),
            'status' => $webhook->status()->value,
            'last_delivered_at' => $webhook->lastDeliveredAt()?->format(DATE_ATOM),
        ];
    }

    /** @return array<string, mixed> */
    public function projectAggregate(DomainIdentityProject $project): array
    {
        return [
            'id' => $project->id()->toString(),
            'name' => $project->name(),
            'slug' => $project->slug(),
            'description' => $project->description(),
            'status' => $project->status()->value,
            'mode' => $project->mode()->value,
            'sandbox_ttl_minutes' => $project->sandboxTtlMinutes(),
            'registration_mode' => $project->registrationMode()->value,
            'registration_role_id' => $project->registrationRoleId(),
        ];
    }

    /** @return array<string, mixed> */
    public function identity(
        User $user,
        IdentityProject $project,
        IdentityProjectClient $client,
        IdentityProjectMembership $membership,
    ): array {
        return [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
                'avatar_url' => $user->avatar_url,
                'email_verified' => $user->email_verified_at !== null,
                'is_system_admin' => $user->is_system_admin,
                'is_temporary' => $user->is_temporary,
                'temporary_expires_at' => $user->demo_expires_at?->toIso8601String(),
            ],
            'project' => $this->project($project),
            'client' => $this->client($client),
            'membership' => $this->membership($membership),
        ];
    }

    /** @return array<string, mixed> */
    public function project(IdentityProject $project): array
    {
        return [
            'id' => $project->id,
            'name' => $project->name,
            'slug' => $project->slug,
            'description' => $project->description,
            'status' => $project->status,
            'mode' => $project->mode,
            'sandbox_ttl_minutes' => $project->sandbox_ttl_minutes,
            'registration_mode' => $project->registration_mode,
            'registration_role_id' => $project->registration_role_id,
        ];
    }

    /** @return array<string, mixed> */
    public function client(IdentityProjectClient $client): array
    {
        return [
            'id' => $client->id,
            'project_id' => $client->project_id,
            'name' => $client->name,
            'secret_prefix' => $client->secret_prefix,
            'status' => $client->status,
            'last_used_at' => $client->last_used_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    public function membership(IdentityProjectMembership $membership): array
    {
        return [
            'id' => $membership->id,
            'project_id' => $membership->project_id,
            'user' => $membership->relationLoaded('user')
                ? [
                    'id' => $membership->user->id,
                    'email' => $membership->user->email,
                    'username' => $membership->user->username,
                ]
                : ['id' => $membership->user_id],
            'status' => $membership->status,
            'is_admin' => $membership->is_admin,
            'authorization_version' => $membership->authorization_version,
            'role_ids' => $membership->roles()->pluck('identity_project_roles.id')->all(),
            'direct_permission_ids' => $membership->permissions()->pluck('identity_project_permissions.id')->all(),
            'roles' => $membership->effectiveRoleSlugs(),
            'permissions' => $membership->effectivePermissionKeys(),
        ];
    }

    /** @return array<string, mixed> */
    public function role(IdentityProjectRole $role): array
    {
        return [
            'id' => $role->id,
            'project_id' => $role->project_id,
            'name' => $role->name,
            'slug' => $role->slug,
            'description' => $role->description,
            'permission_ids' => $role->permissions()->pluck('identity_project_permissions.id')->all(),
        ];
    }

    /** @return array<string, mixed> */
    public function permission(IdentityProjectPermission $permission): array
    {
        return [
            'id' => $permission->id,
            'project_id' => $permission->project_id,
            'key' => $permission->key,
            'name' => $permission->name,
            'description' => $permission->description,
            'source' => $permission->source,
            'source_client_id' => $permission->source_client_id,
            'status' => $permission->status,
        ];
    }

    /** @return array<string, mixed> */
    public function webhook(IdentityWebhookEndpoint $endpoint): array
    {
        return [
            'id' => $endpoint->id,
            'project_id' => $endpoint->project_id,
            'url' => $endpoint->url,
            'events' => $endpoint->events,
            'secret_prefix' => $endpoint->secret_prefix,
            'status' => $endpoint->status,
            'last_delivered_at' => $endpoint->last_delivered_at?->toIso8601String(),
        ];
    }
}
