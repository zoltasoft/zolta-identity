<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services;

use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ConfigureIdentityProjectEnvironment;
use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ConfigureIdentityProjectRegistration;
use App\Services\UserManagementService\Application\Contracts\Identity\Projects\CreateIdentityProject;
use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityClients;
use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityHostedApplications;
use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityProjectAccess;
use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityWebhooks;
use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ReadIdentityProjects;
use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ResolveIdentityHostedApplications;
use App\Services\UserManagementService\Application\Exceptions\IdentityAuthorizationException;
use App\Services\UserManagementService\Application\Exceptions\IdentityResourceNotFoundException;
use App\Services\UserManagementService\Domain\Aggregates\IdentityClient as DomainIdentityClient;
use App\Services\UserManagementService\Domain\Aggregates\IdentityMembership as DomainIdentityMembership;
use App\Services\UserManagementService\Domain\Aggregates\IdentityPermission as DomainIdentityPermission;
use App\Services\UserManagementService\Domain\Aggregates\IdentityProject as DomainIdentityProject;
use App\Services\UserManagementService\Domain\Aggregates\IdentityRole as DomainIdentityRole;
use App\Services\UserManagementService\Domain\Aggregates\IdentityWebhook as DomainIdentityWebhook;
use App\Services\UserManagementService\Domain\Enums\IdentityClientStatus;
use App\Services\UserManagementService\Domain\Enums\IdentityMembershipStatus;
use App\Services\UserManagementService\Domain\Enums\IdentityProjectMode;
use App\Services\UserManagementService\Domain\Enums\IdentityProjectRegistrationMode;
use App\Services\UserManagementService\Domain\Enums\IdentityWebhookStatus;
use App\Services\UserManagementService\Domain\Policies\IdentityAdministrationPolicy;
use App\Services\UserManagementService\Domain\Repositories\IdentityClientRepository;
use App\Services\UserManagementService\Domain\Repositories\IdentityMembershipRepository;
use App\Services\UserManagementService\Domain\Repositories\IdentityPermissionRepository;
use App\Services\UserManagementService\Domain\Repositories\IdentityProjectRepository;
use App\Services\UserManagementService\Domain\Repositories\IdentityRoleRepository;
use App\Services\UserManagementService\Domain\Repositories\IdentityWebhookRepository;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityClientId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityMembershipId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityPermissionId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityRoleId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityWebhookId;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityAuditEvent;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityHostedApplication;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProject;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectClient;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectInvitation;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityAuditEventRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityProjectClientRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityProjectMembershipRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityProjectPermissionRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityProjectRoleRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityWebhookEndpointRepository;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityAuditRecorder;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityAuthorizationGuard;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityPayloadFactory;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityPermissionManifestSynchronizer;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityTokenManager;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityWebhookDestinationValidator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Zolta\Domain\ValueObjects\UserId;

final readonly class EloquentIdentityProjectService implements ConfigureIdentityProjectEnvironment, ConfigureIdentityProjectRegistration, CreateIdentityProject, ManageIdentityClients, ManageIdentityHostedApplications, ManageIdentityProjectAccess, ManageIdentityWebhooks, ReadIdentityProjects, ResolveIdentityHostedApplications
{
    public function __construct(
        private EloquentIdentityProjectMembershipRepository $memberships,
        private EloquentIdentityProjectRoleRepository $projectRoles,
        private EloquentIdentityProjectPermissionRepository $projectPermissions,
        private EloquentIdentityProjectClientRepository $projectClients,
        private EloquentIdentityWebhookEndpointRepository $webhooks,
        private EloquentIdentityAuditEventRepository $auditEvents,
        private IdentityProjectRepository $projects,
        private IdentityClientRepository $clientAggregates,
        private IdentityMembershipRepository $membershipAggregates,
        private IdentityPermissionRepository $permissionAggregates,
        private IdentityRoleRepository $roleAggregates,
        private IdentityWebhookRepository $webhookAggregates,
        private IdentityAdministrationPolicy $administrationPolicy,
        private IdentityAuthorizationGuard $authorization,
        private IdentityPermissionManifestSynchronizer $permissionManifests,
        private IdentityWebhookDestinationValidator $webhookDestinations,
        private IdentityTokenManager $tokens,
        private IdentityPayloadFactory $payloads,
        private IdentityAuditRecorder $audit,
    ) {}

    public function listProjects(string $actorUserId): array
    {
        $user = User::query()->findOrFail($actorUserId);
        $query = IdentityProject::query()->orderBy('name');

        if (! $user->is_system_admin) {
            $query->whereHas(
                'memberships',
                fn (Builder $builder) => $builder
                    ->where('user_id', $actorUserId)
                    ->where('status', 'active'),
            );
        }

        return $query->get()
            ->map(fn (IdentityProject $project) => $this->payloads->project($project))
            ->all();
    }

    public function createProject(string $actorUserId, array $attributes): array
    {
        $this->authorization->assertInstallationAdministrator($actorUserId);
        $user = User::query()->findOrFail($actorUserId);

        return DB::transaction(function () use ($user, $attributes): array {
            $project = DomainIdentityProject::create(
                (string) $attributes['name'],
                (string) $attributes['slug'],
                isset($attributes['description']) ? (string) $attributes['description'] : null,
            );
            $this->projects->save($project);
            $projectId = $project->id()->toString();
            $membership = DomainIdentityMembership::create(
                $project->id(),
                new UserId((string) $user->id),
                true,
            );
            $this->membershipAggregates->save($membership);
            $this->audit->record(
                'project.created',
                $projectId,
                null,
                $user->id,
                'project',
                $projectId,
            );

            return $this->payloads->projectAggregate($project);
        });
    }

    public function updateProjectRegistration(
        string $actorUserId,
        string $projectId,
        string $mode,
        ?string $roleId,
        bool $emailVerificationRequired,
    ): void {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);

        if ($roleId !== null && ! $this->projectRoles->existsForProject($projectId, $roleId)) {
            throw new IdentityAuthorizationException(
                'The default registration role must belong to this project.',
            );
        }

        $project = $this->projects->find(IdentityProjectId::fromString($projectId))
            ?? throw new IdentityResourceNotFoundException('Identity project');
        $project->configureRegistration(
            IdentityProjectRegistrationMode::from($mode),
            $roleId,
            $emailVerificationRequired,
        );
        $this->projects->save($project);
        $this->audit->record(
            'project.registration_updated',
            $projectId,
            null,
            $actorUserId,
            'project',
            $projectId,
            [
                'registration_mode' => $mode,
                'registration_role_id' => $roleId,
                'email_verification_required' => $emailVerificationRequired,
            ],
        );
    }

    public function updateProjectEnvironment(
        string $actorUserId,
        string $projectId,
        string $mode,
        int $sandboxTtlMinutes,
    ): void {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $project = $this->projects->find(IdentityProjectId::fromString($projectId))
            ?? throw new IdentityResourceNotFoundException('Identity project');
        $project->configureEnvironment(IdentityProjectMode::from($mode), $sandboxTtlMinutes);
        $this->projects->save($project);
        $this->audit->record(
            'project.environment_updated',
            $projectId,
            null,
            $actorUserId,
            'project',
            $projectId,
            [
                'mode' => $mode,
                'sandbox_ttl_minutes' => $sandboxTtlMinutes,
            ],
        );
    }

    public function createWebhook(
        string $actorUserId,
        string $projectId,
        string $url,
        array $events,
    ): array {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $this->webhookDestinations->assertValid($url);
        $secret = Str::random(64);
        $webhook = DomainIdentityWebhook::create(
            IdentityProjectId::fromString($projectId),
            $url,
            $events,
            $secret,
            Str::substr($secret, 0, 8),
        );
        $this->webhookAggregates->save($webhook);
        $webhookId = $webhook->id()->toString();
        $this->audit->record(
            'webhook.created',
            $projectId,
            null,
            $actorUserId,
            'webhook',
            $webhookId,
        );

        return $this->payloads->webhookAggregate($webhook) + ['secret' => $secret];
    }

    public function updateWebhook(
        string $actorUserId,
        string $projectId,
        string $webhookId,
        string $url,
        array $events,
        string $status,
    ): void {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $this->webhookDestinations->assertValid($url);
        $webhook = $this->findWebhook($projectId, $webhookId);
        $webhook->configure($url, $events, IdentityWebhookStatus::from($status));
        $this->webhookAggregates->save($webhook);
        $this->audit->record(
            'webhook.updated',
            $projectId,
            null,
            $actorUserId,
            'webhook',
            $webhookId,
        );
    }

    public function rotateWebhookSecret(
        string $actorUserId,
        string $projectId,
        string $webhookId,
    ): array {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $webhook = $this->findWebhook($projectId, $webhookId);
        $secret = Str::random(64);
        $webhook->rotateSecret($secret, Str::substr($secret, 0, 8));
        $this->webhookAggregates->save($webhook);
        $this->audit->record(
            'webhook.secret_rotated',
            $projectId,
            null,
            $actorUserId,
            'webhook',
            $webhookId,
        );

        return $this->payloads->webhookAggregate($webhook) + ['secret' => $secret];
    }

    public function removeWebhook(string $actorUserId, string $projectId, string $webhookId): void
    {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $webhook = $this->findWebhook($projectId, $webhookId);
        $this->webhookAggregates->delete($webhook);
        $this->audit->record(
            'webhook.deleted',
            $projectId,
            null,
            $actorUserId,
            'webhook',
            $webhookId,
        );
    }

    public function projectDetails(string $actorUserId, string $projectId): array
    {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $project = IdentityProject::query()->findOrFail($projectId);

        return $this->payloads->project($project) + [
            'clients' => $this->projectClients
                ->listForProject($projectId, sort: ['name'])
                ->map(fn ($client) => $this->payloads->client($client))
                ->all(),
            'memberships' => $this->memberships
                ->listForProject($projectId, ['user', 'roles', 'permissions'])
                ->map(fn ($membership) => $this->payloads->membership($membership))
                ->all(),
            'roles' => $this->projectRoles
                ->listForProject($projectId, ['permissions'], ['name'])
                ->map(fn ($role) => $this->payloads->role($role))
                ->all(),
            'permissions' => $this->projectPermissions
                ->listForProject($projectId, sort: ['key'])
                ->map(fn ($permission) => $this->payloads->permission($permission))
                ->all(),
            'webhooks' => $this->webhooks
                ->listForProject($projectId, sort: ['url'])
                ->map(fn ($endpoint) => $this->payloads->webhook($endpoint))
                ->all(),
            'hosted_applications' => IdentityHostedApplication::query()
                ->where('project_id', $projectId)
                ->orderBy('name')
                ->get()
                ->map(fn (IdentityHostedApplication $application) => $this->payloads->hostedApplication($application))
                ->all(),
        ];
    }

    public function createHostedApplication(
        string $actorUserId,
        string $projectId,
        array $attributes,
    ): array {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);

        return DB::transaction(function () use ($actorUserId, $attributes, $projectId): array {
            $this->validateHostedApplicationClients($actorUserId, $projectId, $attributes);
            $application = IdentityHostedApplication::query()->create([
                'project_id' => $projectId,
                'primary_client_id' => $attributes['primary_client_id'],
                'sandbox_client_id' => $attributes['sandbox_client_id'] ?? null,
                'key' => $attributes['key'],
                'name' => $attributes['name'],
                'application_url' => $attributes['application_url'],
                'callback_url' => $attributes['callback_url'],
                'appearance' => $this->hostedApplicationAppearance($attributes),
                'status' => 'active',
            ]);
            $this->audit->record(
                'hosted_application.created',
                $projectId,
                $application->primary_client_id,
                $actorUserId,
                'hosted_application',
                $application->id,
                ['key' => $application->key],
            );

            return $this->payloads->hostedApplication($application);
        });
    }

    public function updateHostedApplication(
        string $actorUserId,
        string $projectId,
        string $applicationId,
        array $attributes,
    ): void {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $application = $this->findHostedApplication($projectId, $applicationId);
        $this->validateHostedApplicationClients($actorUserId, $projectId, $attributes);
        $application->fill([
            'name' => $attributes['name'],
            'primary_client_id' => $attributes['primary_client_id'],
            'sandbox_client_id' => $attributes['sandbox_client_id'] ?? null,
            'application_url' => $attributes['application_url'],
            'callback_url' => $attributes['callback_url'],
            'appearance' => $this->hostedApplicationAppearance($attributes),
            'status' => $attributes['status'],
        ])->save();
        $this->audit->record(
            'hosted_application.updated',
            $projectId,
            $application->primary_client_id,
            $actorUserId,
            'hosted_application',
            $application->id,
            ['key' => $application->key, 'status' => $application->status],
        );
    }

    public function removeHostedApplication(
        string $actorUserId,
        string $projectId,
        string $applicationId,
    ): void {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $application = $this->findHostedApplication($projectId, $applicationId);
        $this->audit->record(
            'hosted_application.deleted',
            $projectId,
            $application->primary_client_id,
            $actorUserId,
            'hosted_application',
            $application->id,
            ['key' => $application->key],
        );
        if ($application->logo_path !== null) {
            Storage::disk((string) config('zolta.identity.hosted_applications.branding_disk', 'public'))
                ->delete($application->logo_path);
        }
        $application->delete();
    }

    public function uploadHostedApplicationLogo(
        string $actorUserId,
        string $projectId,
        string $applicationId,
        UploadedFile $logo,
    ): array {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $application = $this->findHostedApplication($projectId, $applicationId);
        $disk = (string) config('zolta.identity.hosted_applications.branding_disk', 'public');
        $extension = $logo->extension() ?: 'png';
        $path = "identity/hosted-applications/{$application->id}/".Str::uuid().".{$extension}";

        Storage::disk($disk)->putFileAs(
            dirname($path),
            $logo,
            basename($path),
            ['visibility' => 'public'],
        );

        $previousPath = $application->logo_path;
        $application->forceFill(['logo_path' => $path])->save();
        if ($previousPath !== null) {
            Storage::disk($disk)->delete($previousPath);
        }

        $this->audit->record(
            'hosted_application.logo_uploaded',
            $projectId,
            $application->primary_client_id,
            $actorUserId,
            'hosted_application',
            $application->id,
            ['key' => $application->key],
        );

        return $this->payloads->hostedApplication($application);
    }

    public function removeHostedApplicationLogo(
        string $actorUserId,
        string $projectId,
        string $applicationId,
    ): void {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $application = $this->findHostedApplication($projectId, $applicationId);
        $path = $application->logo_path;
        $application->forceFill(['logo_path' => null])->save();

        if ($path !== null) {
            Storage::disk((string) config('zolta.identity.hosted_applications.branding_disk', 'public'))->delete($path);
        }

        $this->audit->record(
            'hosted_application.logo_removed',
            $projectId,
            $application->primary_client_id,
            $actorUserId,
            'hosted_application',
            $application->id,
            ['key' => $application->key],
        );
    }

    public function resolveHostedApplication(string $key): array
    {
        return $this->resolvedHostedApplication(
            IdentityHostedApplication::query()
                ->where('key', $key)
                ->first(),
        );
    }

    public function resolveHostedApplicationByClient(string $clientId): array
    {
        return $this->resolvedHostedApplication(
            IdentityHostedApplication::query()
                ->where('primary_client_id', $clientId)
                ->first(),
        );
    }

    public function createClient(string $actorUserId, string $projectId, string $name): array
    {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $secret = Str::random(64);
        $client = DomainIdentityClient::create(
            IdentityProjectId::fromString($projectId),
            $name,
            hash('sha256', $secret),
            Str::substr($secret, 0, 8),
        );
        $this->clientAggregates->save($client);
        $clientId = $client->id()->toString();
        $this->audit->record(
            'client.created',
            $projectId,
            $clientId,
            $actorUserId,
            'client',
            $clientId,
        );

        return $this->payloads->clientAggregate($client) + ['client_secret' => $secret];
    }

    public function rotateClientSecret(
        string $actorUserId,
        string $projectId,
        string $clientId,
    ): array {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $client = $this->findClient($projectId, $clientId);
        $secret = Str::random(64);
        $client->rotateCredentials(
            hash('sha256', $secret),
            Str::substr($secret, 0, 8),
        );
        $this->clientAggregates->save($client);
        $this->audit->record(
            'client.secret_rotated',
            $projectId,
            $clientId,
            $actorUserId,
            'client',
            $clientId,
        );

        return $this->payloads->clientAggregate($client) + ['client_secret' => $secret];
    }

    public function setClientStatus(
        string $actorUserId,
        string $projectId,
        string $clientId,
        string $status,
    ): void {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $client = $this->findClient($projectId, $clientId);
        $clientStatus = IdentityClientStatus::from($status);
        $client->changeStatus($clientStatus);
        $this->clientAggregates->save($client);

        if ($clientStatus !== IdentityClientStatus::Active) {
            $this->tokens->revokeClient($clientId);
        }

        $this->audit->record(
            'client.status_updated',
            $projectId,
            $clientId,
            $actorUserId,
            'client',
            $clientId,
            ['status' => $status],
        );
    }

    public function syncPermissionManifest(
        string $actorUserId,
        string $projectId,
        string $clientId,
        array $manifest,
    ): array {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $this->projectClients->findForProjectOrFail($projectId, $clientId);
        $permissions = $this->permissionManifests->sync($projectId, $clientId, $manifest);
        $this->audit->record(
            'permission_manifest.synced',
            $projectId,
            $clientId,
            $actorUserId,
            'client',
            $clientId,
            ['permission_count' => count($manifest)],
        );

        return $permissions;
    }

    public function createRole(string $actorUserId, string $projectId, array $attributes): array
    {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $role = DomainIdentityRole::create(
            IdentityProjectId::fromString($projectId),
            (string) $attributes['name'],
            (string) $attributes['slug'],
            isset($attributes['description']) ? (string) $attributes['description'] : null,
        );
        $this->roleAggregates->save($role);
        $roleId = $role->id()->toString();
        $this->audit->record('role.created', $projectId, null, $actorUserId, 'role', $roleId);

        return $this->payloads->roleAggregate($role);
    }

    public function createPermission(
        string $actorUserId,
        string $projectId,
        array $attributes,
    ): array {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $key = (string) $attributes['key'];
        $permission = DomainIdentityPermission::createManual(
            IdentityProjectId::fromString($projectId),
            $key,
            isset($attributes['name']) ? (string) $attributes['name'] : $key,
            isset($attributes['description']) ? (string) $attributes['description'] : null,
        );
        $this->permissionAggregates->save($permission);
        $this->membershipAggregates->incrementAuthorizationVersionForProject(
            IdentityProjectId::fromString($projectId),
        );
        $permissionId = $permission->id()->toString();
        $this->audit->record(
            'permission.created',
            $projectId,
            null,
            $actorUserId,
            'permission',
            $permissionId,
        );

        return $this->payloads->permissionAggregate($permission);
    }

    public function setRolePermissions(
        string $actorUserId,
        string $projectId,
        string $roleId,
        array $permissionIds,
    ): void {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $role = $this->findRole($projectId, $roleId);
        $validIds = $this->projectPermissions->existingIdsForProject($projectId, $permissionIds);

        if (count($validIds) !== count(array_unique($permissionIds))) {
            throw new IdentityAuthorizationException('Every permission must belong to the project.');
        }

        $role->assignPermissions(array_map(
            static fn (string $permissionId): IdentityPermissionId => IdentityPermissionId::fromString($permissionId),
            $validIds,
        ));
        $this->roleAggregates->save($role);
        $this->membershipAggregates->incrementAuthorizationVersionForProject(
            IdentityProjectId::fromString($projectId),
        );
        $this->audit->record(
            'role.permissions_updated',
            $projectId,
            null,
            $actorUserId,
            'role',
            $roleId,
        );
    }

    public function invite(
        string $actorUserId,
        string $projectId,
        string $email,
        bool $isAdmin,
    ): array {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $email = Str::lower($email);
        IdentityProjectInvitation::query()
            ->where('project_id', $projectId)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->delete();
        $plainToken = Str::random(64);
        $invitation = IdentityProjectInvitation::query()->create([
            'project_id' => $projectId,
            'invited_by' => $actorUserId,
            'email' => $email,
            'token_hash' => hash('sha256', $plainToken),
            'is_admin' => $isAdmin,
            'expires_at' => now()->addHours((int) config('zolta.identity.invitation_ttl_hours', 72)),
        ]);
        $this->audit->record(
            'invitation.created',
            $projectId,
            null,
            $actorUserId,
            'invitation',
            $invitation->id,
        );

        return [
            'id' => $invitation->id,
            'email' => $invitation->email,
            'is_admin' => $invitation->is_admin,
            'expires_at' => $invitation->expires_at->toIso8601String(),
            'invitation_token' => $plainToken,
        ];
    }

    public function setMembershipAccess(
        string $actorUserId,
        string $projectId,
        string $membershipId,
        array $roleIds,
        array $permissionIds,
        bool $isAdmin,
        string $status,
    ): void {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $membership = $this->findMembership($projectId, $membershipId);
        $actor = User::query()->findOrFail($actorUserId);

        if (! $this->administrationPolicy->canUpdateMembership(
            $actorUserId,
            $membership->userId()->toString(),
            (bool) $actor->is_system_admin,
            $isAdmin,
            $status,
        )) {
            throw new IdentityAuthorizationException(
                'Project administrators cannot suspend or demote their own membership.',
            );
        }

        $roles = $this->projectRoles->existingIdsForProject($projectId, $roleIds);
        $permissions = $this->projectPermissions->existingIdsForProject($projectId, $permissionIds);
        if (count($roles) !== count(array_unique($roleIds))
            || count($permissions) !== count(array_unique($permissionIds))) {
            throw new IdentityAuthorizationException('Roles and permissions must belong to the project.');
        }

        $membership->updateAccess(
            array_map(
                static fn (string $roleId): IdentityRoleId => IdentityRoleId::fromString($roleId),
                $roles,
            ),
            array_map(
                static fn (string $permissionId): IdentityPermissionId => IdentityPermissionId::fromString($permissionId),
                $permissions,
            ),
            $isAdmin,
            IdentityMembershipStatus::from($status),
        );
        $this->membershipAggregates->save($membership);
        $this->audit->record(
            'membership.access_updated',
            $projectId,
            null,
            $actorUserId,
            'membership',
            $membershipId,
        );
    }

    public function removeMembership(
        string $actorUserId,
        string $projectId,
        string $membershipId,
    ): void {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);
        $membership = $this->findMembership($projectId, $membershipId);

        if (! $this->administrationPolicy->canRemoveMembership(
            $actorUserId,
            $membership->userId()->toString(),
            $membership->isAdministrator(),
        )) {
            throw new IdentityAuthorizationException(
                'Project administrators cannot remove their own membership.',
            );
        }

        $userId = $membership->userId()->toString();
        $this->membershipAggregates->delete($membership);
        $this->tokens->revokeProjectUser($projectId, $userId);
        $this->audit->record(
            'membership.removed',
            $projectId,
            null,
            $actorUserId,
            'membership',
            $membershipId,
        );
    }

    public function listAuditEvents(
        string $actorUserId,
        string $projectId,
        int $limit = 100,
    ): array {
        $this->authorization->assertProjectAdministrator($actorUserId, $projectId);

        return $this->auditEvents
            ->listForProject(
                $projectId,
                sort: ['-created_at'],
                limit: min(max($limit, 1), 250),
            )
            ->map(fn (IdentityAuditEvent $event) => [
                'id' => $event->id,
                'event' => $event->event,
                'actor_user_id' => $event->actor_user_id,
                'client_id' => $event->client_id,
                'target_type' => $event->target_type,
                'target_id' => $event->target_id,
                'metadata' => $event->metadata ?? [],
                'ip_address' => $event->ip_address,
                'created_at' => $event->created_at?->toIso8601String(),
            ])->all();
    }

    private function findClient(string $projectId, string $clientId): DomainIdentityClient
    {
        return $this->clientAggregates->findForProject(
            IdentityProjectId::fromString($projectId),
            IdentityClientId::fromString($clientId),
        ) ?? throw new IdentityResourceNotFoundException('Identity project client');
    }

    private function findHostedApplication(string $projectId, string $applicationId): IdentityHostedApplication
    {
        return IdentityHostedApplication::query()
            ->where('project_id', $projectId)
            ->find($applicationId)
            ?? throw new IdentityResourceNotFoundException('Identity hosted application');
    }

    /** @return array<string, mixed> */
    private function resolvedHostedApplication(?IdentityHostedApplication $application): array
    {
        if ($application === null || $application->status !== 'active') {
            throw new IdentityResourceNotFoundException('Identity hosted application');
        }

        $application->loadMissing(['project', 'primaryClient', 'sandboxClient.project']);
        $client = $application->primaryClient;
        if ($client === null || $client->status !== 'active') {
            throw new IdentityResourceNotFoundException('Identity hosted application');
        }

        return [
            'key' => $application->key,
            'name' => $application->name,
            'application_url' => $application->application_url,
            'callback_url' => $application->callback_url,
            'appearance' => $this->payloads->hostedApplication($application)['appearance'],
            'primary' => [
                'project' => $application->project->slug,
                'client_id' => $client->id,
            ],
            'sandbox' => $application->sandboxClient === null ? null : [
                'project' => $application->sandboxClient->project->slug,
                'client_id' => $application->sandboxClient->id,
            ],
        ];
    }

    /** @param array<string, mixed> $attributes @return array{welcome_text: string|null, accent_color: string|null, background_preset: string} */
    private function hostedApplicationAppearance(array $attributes): array
    {
        $appearance = is_array($attributes['appearance'] ?? null) ? $attributes['appearance'] : [];

        return [
            'welcome_text' => isset($appearance['welcome_text']) && $appearance['welcome_text'] !== ''
                ? (string) $appearance['welcome_text']
                : null,
            'accent_color' => isset($appearance['accent_color']) && $appearance['accent_color'] !== ''
                ? (string) $appearance['accent_color']
                : null,
            'background_preset' => (string) ($appearance['background_preset'] ?? 'identity'),
        ];
    }

    /** @param array<string, mixed> $attributes */
    private function validateHostedApplicationClients(
        string $actorUserId,
        string $projectId,
        array $attributes,
    ): void {
        $primary = IdentityProjectClient::query()
            ->with('project')
            ->where('project_id', $projectId)
            ->find($attributes['primary_client_id']);
        if ($primary === null || $primary->status !== 'active' || $primary->project->mode === 'sandbox') {
            throw new IdentityAuthorizationException('The primary client must be active and belong to this non-sandbox project.');
        }

        $sandboxClientId = $attributes['sandbox_client_id'] ?? null;
        if ($sandboxClientId === null) {
            return;
        }

        $sandbox = IdentityProjectClient::query()->with('project')->find($sandboxClientId);
        if ($sandbox === null || $sandbox->status !== 'active' || $sandbox->project->mode !== 'sandbox') {
            throw new IdentityAuthorizationException('The sandbox client must be active and belong to a sandbox project.');
        }

        $this->authorization->assertProjectAdministrator($actorUserId, $sandbox->project_id);
    }

    private function findMembership(
        string $projectId,
        string $membershipId,
    ): DomainIdentityMembership {
        return $this->membershipAggregates->findForProject(
            IdentityProjectId::fromString($projectId),
            IdentityMembershipId::fromString($membershipId),
        ) ?? throw new IdentityResourceNotFoundException('Identity project membership');
    }

    private function findRole(string $projectId, string $roleId): DomainIdentityRole
    {
        return $this->roleAggregates->findForProject(
            IdentityProjectId::fromString($projectId),
            IdentityRoleId::fromString($roleId),
        ) ?? throw new IdentityResourceNotFoundException('Identity project role');
    }

    private function findWebhook(string $projectId, string $webhookId): DomainIdentityWebhook
    {
        return $this->webhookAggregates->findForProject(
            IdentityProjectId::fromString($projectId),
            IdentityWebhookId::fromString($webhookId),
        ) ?? throw new IdentityResourceNotFoundException('Identity webhook endpoint');
    }
}
