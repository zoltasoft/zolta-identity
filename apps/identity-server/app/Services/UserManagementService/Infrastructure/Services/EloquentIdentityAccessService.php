<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services;

use App\Services\UserManagementService\API\Exceptions\IdentityAuthenticationException;
use App\Services\UserManagementService\API\Exceptions\IdentityAuthorizationException;
use App\Services\UserManagementService\Application\Contracts\IdentityAccessServiceInterface;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityAuditEvent;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProject;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectClient;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectInvitation;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectMembership;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectPermission;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectRole;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityRefreshToken;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityWebhookEndpoint;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\Role;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use App\Services\UserManagementService\Infrastructure\Notifications\ResetIdentityPassword;
use App\Services\UserManagementService\Infrastructure\Notifications\VerifyEmailCode;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityAuditEventRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityProjectClientRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityProjectMembershipRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityProjectPermissionRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityProjectRoleRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityWebhookEndpointRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

final class EloquentIdentityAccessService implements IdentityAccessServiceInterface
{
    public function __construct(
        private readonly EloquentIdentityProjectMembershipRepository $memberships,
        private readonly EloquentIdentityProjectRoleRepository $projectRoles,
        private readonly EloquentIdentityProjectPermissionRepository $projectPermissions,
        private readonly EloquentIdentityProjectClientRepository $projectClients,
        private readonly EloquentIdentityWebhookEndpointRepository $webhooks,
        private readonly EloquentIdentityAuditEventRepository $auditEvents,
    ) {}

    public function listInstallationUsers(string $actorUserId): array
    {
        $this->assertSystemAdmin($actorUserId);

        return User::query()->withCount('identityMemberships')->orderBy('email')->get()->map(fn (User $user) => [
            'id' => $user->id,
            'email' => $user->email,
            'username' => $user->username,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'is_system_admin' => $user->is_system_admin,
            'locked' => $user->locked,
            'project_count' => $user->identity_memberships_count,
            'created_at' => $user->created_at?->toIso8601String(),
        ])->all();
    }

    public function updateInstallationUser(string $actorUserId, string $userId, bool $isSystemAdmin, bool $locked): void
    {
        $this->assertSystemAdmin($actorUserId);
        if ($actorUserId === $userId && (! $isSystemAdmin || $locked)) {
            throw new IdentityAuthorizationException('Installation administrators cannot lock or demote their own account.');
        }

        $user = User::query()->findOrFail($userId);
        $user->forceFill(['is_system_admin' => $isSystemAdmin, 'locked' => $locked])->save();
        if ($locked) {
            IdentityRefreshToken::query()->where('user_id', $userId)->pluck('family_id')->unique()->each(fn (string $familyId) => $this->revokeFamily($familyId));
            PersonalAccessToken::query()->where('tokenable_id', $userId)->delete();
        }
        $this->audit('installation_user.updated', null, null, $actorUserId, 'user', $userId, [
            'is_system_admin' => $isSystemAdmin,
            'locked' => $locked,
        ]);
    }

    public function login(array $credentials, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        $client = $this->authenticateClient((string) $credentials['client_id'], (string) $credentials['client_secret']);
        $project = $client->project;
        if ($project->mode === 'sandbox') {
            throw new IdentityAuthorizationException('Sandbox projects only accept temporary sessions.');
        }
        $requestedProject = (string) ($credentials['project'] ?? '');

        if ($requestedProject !== '' && ! in_array($requestedProject, [$project->id, $project->slug], true)) {
            throw new IdentityAuthenticationException('Invalid credentials.');
        }

        $user = User::query()->whereRaw('lower(email) = ?', [Str::lower((string) $credentials['email'])])->first();
        $isLocked = $user?->locked && ($user->lock_expiry === null || $user->lock_expiry->isFuture());
        if (! $user || $isLocked || ! Hash::check((string) $credentials['password'], $user->password)) {
            $this->audit('auth.login_failed', $project->id, $client->id, null, null, null, [], $ipAddress, $userAgent);
            throw new IdentityAuthenticationException('Invalid credentials.');
        }
        if ($user->locked) {
            $user->forceFill(['locked' => false, 'lock_expiry' => null])->save();
        }

        $membership = $this->memberships->findActiveForProjectUser($project->id, $user->id);

        if (! $membership || $project->status !== 'active') {
            throw new IdentityAuthenticationException('This account does not have access to the project.');
        }

        $tokens = $this->issueTokenPair($user, $project, $client, $membership);
        $this->audit('auth.login_succeeded', $project->id, $client->id, $user->id, 'user', $user->id, [], $ipAddress, $userAgent);

        return $tokens + ['identity' => $this->identityPayload($user, $project, $client, $membership)];
    }

    public function authenticationContext(string $clientId, string $clientSecret, ?string $requestedProject = null): array
    {
        $client = $this->authenticateClient($clientId, $clientSecret);
        $project = $client->project;

        if ($requestedProject !== null
            && $requestedProject !== ''
            && ! in_array($requestedProject, [$project->id, $project->slug], true)) {
            throw new IdentityAuthenticationException('Invalid client credentials.');
        }

        return [
            'project' => $this->projectPayload($project),
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
            ],
        ];
    }

    public function register(array $attributes, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        $client = $this->authenticateClient((string) $attributes['client_id'], (string) $attributes['client_secret']);
        $project = $client->project;
        if ($project->mode === 'sandbox') {
            throw new IdentityAuthorizationException('Sandbox projects do not accept permanent registrations.');
        }
        $requestedProject = (string) ($attributes['project'] ?? '');
        if ($requestedProject !== '' && ! in_array($requestedProject, [$project->id, $project->slug], true)) {
            throw new IdentityAuthenticationException('Invalid credentials.');
        }
        if ($project->status !== 'active' || $project->registration_mode !== 'public') {
            throw new IdentityAuthorizationException('Public registration is not enabled for this project.');
        }

        return DB::transaction(function () use ($attributes, $project, $client, $ipAddress, $userAgent): array {
            $email = Str::lower((string) $attributes['email']);
            if (User::query()->whereRaw('lower(email) = ?', [$email])->exists()) {
                throw new IdentityAuthenticationException('An account with this email already exists. Sign in instead.');
            }

            $user = User::query()->create([
                'username' => (string) $attributes['username'],
                'email' => $email,
                'password' => (string) $attributes['password'],
                'role_id' => Role::query()->firstOrCreate(
                    ['role' => 'User'],
                    ['description' => 'Default global identity role'],
                )->id,
                'terms' => 'accepted',
            ]);
            $membership = IdentityProjectMembership::query()->create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'status' => 'active',
                'is_admin' => false,
            ]);
            if ($project->registration_role_id !== null) {
                $roleExists = $this->projectRoles->existsForProject($project->id, $project->registration_role_id);
                if ($roleExists) {
                    $membership->roles()->attach($project->registration_role_id);
                }
            }

            $tokens = $this->issueTokenPair($user, $project, $client, $membership);
            $this->issueEmailVerification($user);
            $this->audit('auth.registered', $project->id, $client->id, $user->id, 'user', $user->id, [], $ipAddress, $userAgent);

            return $tokens + ['identity' => $this->identityPayload($user, $project, $client, $membership)];
        });
    }

    public function createSandboxSession(string $clientId, string $clientSecret, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        $client = $this->authenticateClient($clientId, $clientSecret);
        $project = $client->project;
        if ($project->mode !== 'sandbox') {
            throw new IdentityAuthorizationException('Temporary sessions are only available for sandbox projects.');
        }

        return DB::transaction(function () use ($project, $client, $ipAddress, $userAgent): array {
            $id = (string) Str::uuid();
            $suffix = Str::lower(Str::random(8));
            $expiresAt = now()->addMinutes((int) $project->sandbox_ttl_minutes);
            $user = User::query()->create([
                'id' => $id,
                'username' => "Sandbox Guest {$suffix}",
                'email' => "sandbox-{$id}@identity.invalid",
                'password' => Str::random(64),
                'role_id' => Role::query()->firstOrCreate(
                    ['role' => 'User'],
                    ['description' => 'Default global identity role'],
                )->id,
                'terms' => 'accepted',
                'email_verified_at' => now(),
                'is_temporary' => true,
                'demo_expires_at' => $expiresAt,
                'login_alerts_enabled' => false,
            ]);
            $membership = IdentityProjectMembership::query()->create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'status' => 'active',
                'is_admin' => false,
            ]);
            if ($project->registration_role_id !== null
                && $this->projectRoles->existsForProject($project->id, $project->registration_role_id)) {
                $membership->roles()->attach($project->registration_role_id);
            }

            $tokens = $this->issueTokenPair($user, $project, $client, $membership);
            $this->audit('auth.sandbox_session_created', $project->id, $client->id, $user->id, 'user', $user->id, [
                'expires_at' => $expiresAt->toIso8601String(),
            ], $ipAddress, $userAgent);

            return $tokens + [
                'is_temporary' => true,
                'expires_at' => $expiresAt->toIso8601String(),
                'identity' => $this->identityPayload($user, $project, $client, $membership),
            ];
        });
    }

    public function resendEmailVerification(string $userId): array
    {
        $user = User::query()->findOrFail($userId);
        if ($user->email_verified_at !== null) {
            return ['message' => 'Email address is already verified.'];
        }

        $code = $this->issueEmailVerification($user);
        $result = ['message' => 'A new verification code was sent.'];
        if ((bool) config('identity.expose_development_tokens', false)) {
            $result['development_code'] = $code;
        }

        return $result;
    }

    public function verifyEmail(string $userId, string $code): void
    {
        $user = User::query()->findOrFail($userId);
        if ($user->email_verified_at !== null) {
            return;
        }
        if ($user->email_verification_expires_at === null
            || $user->email_verification_expires_at->isPast()
            || ! hash_equals((string) $user->email_verification_code_hash, $this->secretHash($code))) {
            throw new IdentityAuthenticationException('The verification code is invalid or expired.');
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'email_verification_code_hash' => null,
            'email_verification_expires_at' => null,
        ])->save();
        $this->audit('auth.email_verified', null, null, $user->id, 'user', $user->id);
    }

    public function requestPasswordReset(string $clientId, string $clientSecret, string $email): array
    {
        $this->authenticateClient($clientId, $clientSecret);
        $result = ['message' => 'If that account exists, password reset instructions were sent.'];
        $user = User::query()->whereRaw('lower(email) = ?', [Str::lower($email)])->first();
        if (! $user) {
            return $result;
        }

        $token = Str::random(64);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => $this->secretHash($token), 'created_at' => now()],
        );
        $user->notify(new ResetIdentityPassword($token));
        if ((bool) config('identity.expose_development_tokens', false)) {
            $result['development_token'] = $token;
        }

        return $result;
    }

    public function resetPassword(string $clientId, string $clientSecret, string $email, string $token, string $password): void
    {
        $this->authenticateClient($clientId, $clientSecret);
        $user = User::query()->whereRaw('lower(email) = ?', [Str::lower($email)])->first();
        $reset = $user ? DB::table('password_reset_tokens')->where('email', $user->email)->first() : null;
        $expiresAt = $reset?->created_at
            ? Carbon::parse($reset->created_at)->addMinutes((int) config('identity.password_reset_ttl_minutes', 60))
            : null;
        if (! $user || ! $reset || $expiresAt?->isPast() !== false
            || ! hash_equals((string) $reset->token, $this->secretHash($token))) {
            throw new IdentityAuthenticationException('The password reset token is invalid or expired.');
        }

        DB::transaction(function () use ($user, $password): void {
            $user->forceFill(['password' => $password])->save();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            IdentityRefreshToken::query()->where('user_id', $user->id)->pluck('family_id')->unique()
                ->each(fn (string $familyId) => $this->revokeFamily($familyId));
            PersonalAccessToken::query()->where('tokenable_id', $user->id)->delete();
            $this->audit('auth.password_reset', null, null, $user->id, 'user', $user->id);
        });
    }

    public function refresh(array $credentials, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        $client = $this->authenticateClient((string) $credentials['client_id'], (string) $credentials['client_secret']);
        $plainRefreshToken = (string) $credentials['refresh_token'];

        $result = DB::transaction(function () use ($client, $plainRefreshToken, $ipAddress, $userAgent): array {
            $refresh = IdentityRefreshToken::query()
                ->where('token_hash', hash('sha256', $plainRefreshToken))
                ->lockForUpdate()
                ->first();

            if (! $refresh || $refresh->client_id !== $client->id) {
                throw new IdentityAuthenticationException('Invalid refresh token.');
            }

            if ($refresh->rotated_to_id !== null || $refresh->used_at !== null) {
                $this->revokeFamily($refresh->family_id);
                $this->audit('auth.refresh_replay_detected', $refresh->project_id, $refresh->client_id, $refresh->user_id, 'session', $refresh->family_id, [], $ipAddress, $userAgent);

                return ['replay_detected' => true];
            }

            if ($refresh->revoked_at !== null || $refresh->expires_at->isPast()) {
                throw new IdentityAuthenticationException('Refresh token is expired or revoked.');
            }

            $user = User::query()->findOrFail($refresh->user_id);
            $project = IdentityProject::query()->where('status', 'active')->findOrFail($refresh->project_id);
            $membership = $this->memberships->findActiveForProjectUser($project->id, $user->id);
            if (! $membership) {
                $this->revokeFamily($refresh->family_id);
                throw new IdentityAuthenticationException('Project access has been revoked.');
            }

            $tokens = $this->issueTokenPair($user, $project, $client, $membership, $refresh->family_id);
            $newRefresh = IdentityRefreshToken::query()->where('token_hash', hash('sha256', $tokens['refresh_token']))->firstOrFail();
            $refresh->forceFill(['used_at' => now(), 'rotated_to_id' => $newRefresh->id])->save();
            $this->audit('auth.token_refreshed', $project->id, $client->id, $user->id, 'session', $refresh->family_id, [], $ipAddress, $userAgent);

            return $tokens + ['identity' => $this->identityPayload($user, $project, $client, $membership)];
        });

        if (($result['replay_detected'] ?? false) === true) {
            throw new IdentityAuthenticationException('Refresh token reuse detected. The session has been revoked.');
        }

        return $result;
    }

    public function introspect(string $clientId, string $clientSecret, string $accessToken): array
    {
        $client = $this->authenticateClient($clientId, $clientSecret);
        $token = PersonalAccessToken::findToken($accessToken);

        if (! $token || $token->expires_at?->isPast() || $token->identity_project_id !== $client->project_id) {
            return ['active' => false];
        }

        $membership = $this->memberships->findActiveForProjectUser(
            (string) $token->identity_project_id,
            (string) $token->tokenable_id,
        );

        if (! $membership) {
            return ['active' => false];
        }

        $user = User::query()->find($token->tokenable_id);
        if (! $user || ($user->is_temporary && ($user->demo_expires_at === null || $user->demo_expires_at->isPast()))) {
            return ['active' => false];
        }

        $token->forceFill(['last_used_at' => now()])->save();
        $client->forceFill(['last_used_at' => now()])->save();

        return [
            'active' => true,
            'sub' => $user->id,
            'email' => $user->email,
            'username' => $user->username,
            'email_verified' => $user->email_verified_at !== null,
            'project_id' => $token->identity_project_id,
            'project_slug' => $client->project->slug,
            'project_mode' => $client->project->mode,
            'client_id' => $token->identity_client_id,
            'session_id' => $token->identity_refresh_family_id,
            'roles' => $membership->effectiveRoleSlugs(),
            'permissions' => $membership->effectivePermissionKeys(),
            'authorization_version' => $membership->authorization_version,
            'is_temporary' => $user->is_temporary,
            'temporary_expires_at' => $user->demo_expires_at?->toIso8601String(),
            'exp' => $token->expires_at?->getTimestamp(),
        ];
    }

    public function logout(string $accessToken): void
    {
        $token = PersonalAccessToken::findToken($accessToken);
        if (! $token) {
            return;
        }

        if ($token->identity_refresh_family_id) {
            $this->revokeFamily($token->identity_refresh_family_id);
        } else {
            $token->delete();
        }
    }

    public function currentIdentity(string $userId, string $accessToken): array
    {
        $token = PersonalAccessToken::findToken($accessToken);
        if (! $token || (string) $token->tokenable_id !== $userId || ! $token->identity_project_id) {
            throw new IdentityAuthenticationException('Invalid access token.');
        }

        $user = User::query()->findOrFail($userId);
        $project = IdentityProject::query()->findOrFail($token->identity_project_id);
        $client = $this->projectClients->findForProjectOrFail($project->id, (string) $token->identity_client_id);
        $membership = $this->memberships->findActiveForProjectUser($project->id, $userId);
        if ($membership === null) {
            throw (new ModelNotFoundException)->setModel(
                IdentityProjectMembership::class,
                [$userId],
            );
        }

        return $this->identityPayload($user, $project, $client, $membership);
    }

    public function listSessions(string $userId, string $accessToken): array
    {
        $currentFamily = PersonalAccessToken::findToken($accessToken)?->identity_refresh_family_id;

        return IdentityRefreshToken::query()
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->get()
            ->unique('family_id')
            ->map(function (IdentityRefreshToken $token) use ($currentFamily): array {
                $project = IdentityProject::query()->find($token->project_id);
                $client = $project
                    ? $this->projectClients->findForProject($project->id, $token->client_id)
                    : null;

                return [
                    'id' => $token->family_id,
                    'current' => $token->family_id === $currentFamily,
                    'project' => $project ? $this->projectPayload($project) : null,
                    'client' => $client ? $this->clientPayload($client) : null,
                    'created_at' => $token->created_at?->toIso8601String(),
                    'expires_at' => $token->expires_at->toIso8601String(),
                ];
            })->values()->all();
    }

    public function revokeSession(string $userId, string $familyId): void
    {
        $owned = IdentityRefreshToken::query()->where('user_id', $userId)->where('family_id', $familyId)->exists();
        if (! $owned) {
            throw new IdentityAuthorizationException('The session does not belong to the authenticated user.');
        }
        $this->revokeFamily($familyId);
        $this->audit('auth.session_revoked', null, null, $userId, 'session', $familyId);
    }

    public function listProjects(string $actorUserId): array
    {
        $user = User::query()->findOrFail($actorUserId);
        $query = IdentityProject::query()->orderBy('name');
        if (! $user->is_system_admin) {
            $query->whereHas('memberships', fn (Builder $builder) => $builder->where('user_id', $actorUserId)->where('status', 'active'));
        }

        return $query->get()->map(fn (IdentityProject $project) => $this->projectPayload($project))->all();
    }

    public function createProject(string $actorUserId, array $attributes): array
    {
        $user = User::query()->findOrFail($actorUserId);
        if (! $user->is_system_admin) {
            throw new IdentityAuthorizationException('Only an installation administrator may create projects.');
        }

        return DB::transaction(function () use ($user, $attributes): array {
            $project = IdentityProject::query()->create([
                'name' => $attributes['name'],
                'slug' => $attributes['slug'],
                'description' => $attributes['description'] ?? null,
                'status' => 'active',
                'registration_mode' => 'invite_only',
            ]);
            IdentityProjectMembership::query()->create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'status' => 'active',
                'is_admin' => true,
            ]);
            $this->audit('project.created', $project->id, null, $user->id, 'project', $project->id);

            return $this->projectPayload($project);
        });
    }

    public function updateProjectRegistration(string $actorUserId, string $projectId, string $mode, ?string $roleId): void
    {
        $this->assertProjectAdmin($actorUserId, $projectId);
        if ($roleId !== null && ! $this->projectRoles->existsForProject($projectId, $roleId)) {
            throw new IdentityAuthorizationException('The default registration role must belong to this project.');
        }

        IdentityProject::query()->findOrFail($projectId)->forceFill([
            'registration_mode' => $mode,
            'registration_role_id' => $roleId,
        ])->save();
        $this->audit('project.registration_updated', $projectId, null, $actorUserId, 'project', $projectId, [
            'registration_mode' => $mode,
            'registration_role_id' => $roleId,
        ]);
    }

    public function updateProjectEnvironment(string $actorUserId, string $projectId, string $mode, int $sandboxTtlMinutes): void
    {
        $this->assertProjectAdmin($actorUserId, $projectId);
        $project = IdentityProject::query()->findOrFail($projectId);
        $project->forceFill([
            'mode' => $mode,
            'sandbox_ttl_minutes' => $sandboxTtlMinutes,
        ])->save();
        $this->audit('project.environment_updated', $projectId, null, $actorUserId, 'project', $projectId, [
            'mode' => $mode,
            'sandbox_ttl_minutes' => $sandboxTtlMinutes,
        ]);
    }

    public function createWebhook(string $actorUserId, string $projectId, string $url, array $events): array
    {
        $this->assertProjectAdmin($actorUserId, $projectId);
        $this->assertWebhookUrl($url);
        $secret = Str::random(64);
        $endpoint = IdentityWebhookEndpoint::query()->create([
            'project_id' => $projectId,
            'url' => $url,
            'events' => array_values(array_unique($events)),
            'secret' => $secret,
            'secret_prefix' => Str::substr($secret, 0, 8),
            'status' => 'active',
        ]);
        $this->audit('webhook.created', $projectId, null, $actorUserId, 'webhook', $endpoint->id);

        return $this->webhookPayload($endpoint) + ['secret' => $secret];
    }

    public function updateWebhook(string $actorUserId, string $projectId, string $webhookId, string $url, array $events, string $status): void
    {
        $this->assertProjectAdmin($actorUserId, $projectId);
        $this->assertWebhookUrl($url);
        $endpoint = $this->webhooks->findForProjectOrFail($projectId, $webhookId);
        $endpoint->forceFill([
            'url' => $url,
            'events' => array_values(array_unique($events)),
            'status' => $status,
        ])->save();
        $this->audit('webhook.updated', $projectId, null, $actorUserId, 'webhook', $endpoint->id);
    }

    public function rotateWebhookSecret(string $actorUserId, string $projectId, string $webhookId): array
    {
        $this->assertProjectAdmin($actorUserId, $projectId);
        $endpoint = $this->webhooks->findForProjectOrFail($projectId, $webhookId);
        $secret = Str::random(64);
        $endpoint->forceFill(['secret' => $secret, 'secret_prefix' => Str::substr($secret, 0, 8)])->save();
        $this->audit('webhook.secret_rotated', $projectId, null, $actorUserId, 'webhook', $endpoint->id);

        return $this->webhookPayload($endpoint) + ['secret' => $secret];
    }

    public function removeWebhook(string $actorUserId, string $projectId, string $webhookId): void
    {
        $this->assertProjectAdmin($actorUserId, $projectId);
        $endpoint = $this->webhooks->findForProjectOrFail($projectId, $webhookId);
        $endpoint->delete();
        $this->audit('webhook.deleted', $projectId, null, $actorUserId, 'webhook', $webhookId);
    }

    public function projectDetails(string $actorUserId, string $projectId): array
    {
        $this->assertProjectAdmin($actorUserId, $projectId);
        $project = IdentityProject::query()->findOrFail($projectId);

        return $this->projectPayload($project) + [
            'clients' => $this->projectClients->listForProject($projectId, sort: ['name'])->map(fn ($client) => $this->clientPayload($client))->all(),
            'memberships' => $this->memberships->listForProject($projectId, ['user', 'roles', 'permissions'])->map(fn ($membership) => $this->membershipPayload($membership))->all(),
            'roles' => $this->projectRoles->listForProject($projectId, ['permissions'], ['name'])->map(fn ($role) => $this->rolePayload($role))->all(),
            'permissions' => $this->projectPermissions->listForProject($projectId, sort: ['key'])->map(fn ($permission) => $this->permissionPayload($permission))->all(),
            'webhooks' => $this->webhooks->listForProject($projectId, sort: ['url'])->map(fn ($endpoint) => $this->webhookPayload($endpoint))->all(),
        ];
    }

    public function createClient(string $actorUserId, string $projectId, string $name): array
    {
        $this->assertProjectAdmin($actorUserId, $projectId);
        [$client, $secret] = $this->newClient($projectId, $name);
        $this->audit('client.created', $projectId, $client->id, $actorUserId, 'client', $client->id);

        return $this->clientPayload($client) + ['client_secret' => $secret];
    }

    public function rotateClientSecret(string $actorUserId, string $projectId, string $clientId): array
    {
        $this->assertProjectAdmin($actorUserId, $projectId);
        $client = $this->projectClients->findForProjectOrFail($projectId, $clientId);
        $secret = Str::random(64);
        $client->forceFill(['secret_hash' => hash('sha256', $secret), 'secret_prefix' => Str::substr($secret, 0, 8)])->save();
        $this->audit('client.secret_rotated', $projectId, $client->id, $actorUserId, 'client', $client->id);

        return $this->clientPayload($client) + ['client_secret' => $secret];
    }

    public function setClientStatus(string $actorUserId, string $projectId, string $clientId, string $status): void
    {
        $this->assertProjectAdmin($actorUserId, $projectId);
        $client = $this->projectClients->findForProjectOrFail($projectId, $clientId);
        $client->forceFill(['status' => $status])->save();
        if ($status !== 'active') {
            IdentityRefreshToken::query()->where('client_id', $clientId)->pluck('family_id')->unique()->each(fn (string $familyId) => $this->revokeFamily($familyId));
            PersonalAccessToken::query()->where('identity_client_id', $clientId)->delete();
        }
        $this->audit('client.status_updated', $projectId, $clientId, $actorUserId, 'client', $clientId, ['status' => $status]);
    }

    public function syncPermissionManifest(string $actorUserId, string $projectId, string $clientId, array $manifest): array
    {
        $this->assertProjectAdmin($actorUserId, $projectId);
        $this->projectClients->findForProjectOrFail($projectId, $clientId);
        $permissions = $this->persistPermissionManifest($projectId, $clientId, $manifest);
        $this->audit('permission_manifest.synced', $projectId, $clientId, $actorUserId, 'client', $clientId, ['permission_count' => count($manifest)]);

        return $permissions;
    }

    public function syncOwnPermissionManifest(string $clientId, string $clientSecret, array $manifest): array
    {
        $client = $this->authenticateClient($clientId, $clientSecret);
        $permissions = $this->persistPermissionManifest($client->project_id, $client->id, $manifest);
        $this->audit('permission_manifest.synced', $client->project_id, $client->id, null, 'client', $client->id, ['permission_count' => count($manifest)]);

        return $permissions;
    }

    /** @param list<array{key: string, name?: string, description?: string}> $manifest @return list<array<string, mixed>> */
    private function persistPermissionManifest(string $projectId, string $clientId, array $manifest): array
    {
        $keys = collect($manifest)->pluck('key')->unique()->values();

        DB::transaction(function () use ($projectId, $clientId, $manifest, $keys): void {
            IdentityProjectPermission::query()
                ->where('project_id', $projectId)->where('source_client_id', $clientId)->whereNotIn('key', $keys)->update(['status' => 'stale']);
            foreach ($manifest as $item) {
                IdentityProjectPermission::query()->updateOrCreate(
                    ['project_id' => $projectId, 'key' => $item['key']],
                    [
                        'source_client_id' => $clientId,
                        'name' => $item['name'] ?? $item['key'],
                        'description' => $item['description'] ?? null,
                        'source' => 'manifest',
                        'status' => 'active',
                    ],
                );
            }
        });
        $this->touchProjectMemberships($projectId);

        return $this->projectPermissions
            ->listForProject($projectId, sort: ['key'])
            ->map(fn ($permission) => $this->permissionPayload($permission))
            ->all();
    }

    public function createRole(string $actorUserId, string $projectId, array $attributes): array
    {
        $this->assertProjectAdmin($actorUserId, $projectId);
        $role = IdentityProjectRole::query()->create([
            'project_id' => $projectId,
            'name' => $attributes['name'],
            'slug' => $attributes['slug'],
            'description' => $attributes['description'] ?? null,
        ]);
        $this->audit('role.created', $projectId, null, $actorUserId, 'role', $role->id);

        return $this->rolePayload($role);
    }

    public function createPermission(string $actorUserId, string $projectId, array $attributes): array
    {
        $this->assertProjectAdmin($actorUserId, $projectId);
        $permission = IdentityProjectPermission::query()->create([
            'project_id' => $projectId,
            'key' => $attributes['key'],
            'name' => $attributes['name'] ?? $attributes['key'],
            'description' => $attributes['description'] ?? null,
            'source' => 'manual',
            'status' => 'active',
        ]);
        $this->touchProjectMemberships($projectId);
        $this->audit('permission.created', $projectId, null, $actorUserId, 'permission', $permission->id);

        return $this->permissionPayload($permission);
    }

    public function setRolePermissions(string $actorUserId, string $projectId, string $roleId, array $permissionIds): void
    {
        $this->assertProjectAdmin($actorUserId, $projectId);
        $role = $this->projectRoles->findForProjectOrFail($projectId, $roleId);
        $validIds = $this->projectPermissions->existingIdsForProject($projectId, $permissionIds);
        if (count($validIds) !== count(array_unique($permissionIds))) {
            throw new IdentityAuthorizationException('Every permission must belong to the project.');
        }
        $role->permissions()->sync($validIds);
        $this->touchProjectMemberships($projectId);
        $this->audit('role.permissions_updated', $projectId, null, $actorUserId, 'role', $roleId);
    }

    public function invite(string $actorUserId, string $projectId, string $email, bool $isAdmin): array
    {
        $this->assertProjectAdmin($actorUserId, $projectId);
        $email = Str::lower($email);
        IdentityProjectInvitation::query()->where('project_id', $projectId)->where('email', $email)->whereNull('accepted_at')->delete();
        $plainToken = Str::random(64);
        $invitation = IdentityProjectInvitation::query()->create([
            'project_id' => $projectId,
            'invited_by' => $actorUserId,
            'email' => $email,
            'token_hash' => hash('sha256', $plainToken),
            'is_admin' => $isAdmin,
            'expires_at' => now()->addHours((int) config('identity.invitation_ttl_hours', 72)),
        ]);
        $this->audit('invitation.created', $projectId, null, $actorUserId, 'invitation', $invitation->id);

        return [
            'id' => $invitation->id,
            'email' => $invitation->email,
            'is_admin' => $invitation->is_admin,
            'expires_at' => $invitation->expires_at->toIso8601String(),
            'invitation_token' => $plainToken,
        ];
    }

    public function acceptInvitation(array $attributes): array
    {
        return DB::transaction(function () use ($attributes): array {
            $invitation = IdentityProjectInvitation::query()
                ->where('token_hash', hash('sha256', (string) $attributes['invitation_token']))
                ->lockForUpdate()->first();
            if (! $invitation || $invitation->accepted_at !== null || $invitation->expires_at->isPast()) {
                throw new IdentityAuthenticationException('Invitation is invalid or expired.');
            }

            $user = User::query()->whereRaw('lower(email) = ?', [Str::lower($invitation->email)])->first();
            if (! $user) {
                $roleId = Role::query()->value('id');
                $user = User::query()->create([
                    'id' => (string) Str::uuid(),
                    'username' => $attributes['username'],
                    'email' => $invitation->email,
                    'password' => $attributes['password'],
                    'role_id' => $roleId,
                    'terms' => 'accepted',
                    'email_verified_at' => now(),
                ]);
            } elseif (! Hash::check((string) $attributes['password'], $user->password)) {
                throw new IdentityAuthenticationException('Invalid credentials.');
            }

            $membership = IdentityProjectMembership::query()->updateOrCreate(
                ['project_id' => $invitation->project_id, 'user_id' => $user->id],
                ['status' => 'active', 'is_admin' => $invitation->is_admin],
            );
            $invitation->forceFill(['accepted_at' => now()])->save();
            $this->audit('invitation.accepted', $invitation->project_id, null, $user->id, 'membership', $membership->id);

            return ['membership_id' => $membership->id, 'project_id' => $membership->project_id, 'user_id' => $user->id];
        });
    }

    public function setMembershipAccess(string $actorUserId, string $projectId, string $membershipId, array $roleIds, array $permissionIds, bool $isAdmin, string $status): void
    {
        $this->assertProjectAdmin($actorUserId, $projectId);
        $membership = $this->memberships->findForProjectOrFail($projectId, $membershipId);
        $actor = User::query()->findOrFail($actorUserId);
        if ($membership->user_id === $actorUserId && ! $actor->is_system_admin && (! $isAdmin || $status !== 'active')) {
            throw new IdentityAuthorizationException('Project administrators cannot suspend or demote their own membership.');
        }
        $roles = $this->projectRoles->existingIdsForProject($projectId, $roleIds);
        $permissions = $this->projectPermissions->existingIdsForProject($projectId, $permissionIds);
        if (count($roles) !== count(array_unique($roleIds)) || count($permissions) !== count(array_unique($permissionIds))) {
            throw new IdentityAuthorizationException('Roles and permissions must belong to the project.');
        }

        DB::transaction(function () use ($membership, $roles, $permissions, $isAdmin, $status): void {
            $membership->roles()->sync($roles);
            $membership->permissions()->sync($permissions);
            $membership->forceFill(['is_admin' => $isAdmin, 'status' => $status])->save();
            $membership->touchAuthorization();
        });
        $this->audit('membership.access_updated', $projectId, null, $actorUserId, 'membership', $membershipId);
    }

    public function removeMembership(string $actorUserId, string $projectId, string $membershipId): void
    {
        $this->assertProjectAdmin($actorUserId, $projectId);
        $membership = $this->memberships->findForProjectOrFail($projectId, $membershipId);
        if ($membership->user_id === $actorUserId && $membership->is_admin) {
            throw new IdentityAuthorizationException('Project administrators cannot remove their own membership.');
        }
        $userId = $membership->user_id;
        $membership->delete();
        PersonalAccessToken::query()->where('tokenable_id', $userId)->where('identity_project_id', $projectId)->delete();
        IdentityRefreshToken::query()->where('user_id', $userId)->where('project_id', $projectId)->update(['revoked_at' => now()]);
        $this->audit('membership.removed', $projectId, null, $actorUserId, 'membership', $membershipId);
    }

    public function listAuditEvents(string $actorUserId, string $projectId, int $limit = 100): array
    {
        $this->assertProjectAdmin($actorUserId, $projectId);

        return $this->auditEvents->listForProject(
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

    /** @return array{0: IdentityProjectClient, 1: string} */
    public function newClient(string $projectId, string $name): array
    {
        $secret = Str::random(64);
        $client = IdentityProjectClient::query()->create([
            'project_id' => $projectId,
            'name' => $name,
            'secret_hash' => hash('sha256', $secret),
            'secret_prefix' => Str::substr($secret, 0, 8),
            'status' => 'active',
        ]);

        return [$client, $secret];
    }

    private function authenticateClient(string $clientId, string $clientSecret): IdentityProjectClient
    {
        $client = IdentityProjectClient::query()->with('project')->where('status', 'active')->find($clientId);
        if (! $client || ! hash_equals($client->secret_hash, hash('sha256', $clientSecret)) || $client->project?->status !== 'active') {
            throw new IdentityAuthenticationException('Invalid client credentials.');
        }

        return $client;
    }

    /** @return array<string, mixed> */
    private function issueTokenPair(User $user, IdentityProject $project, IdentityProjectClient $client, IdentityProjectMembership $membership, ?string $familyId = null): array
    {
        $familyId ??= (string) Str::uuid();
        $accessExpiresAt = now()->addMinutes((int) config('identity.access_token_ttl_minutes', 15));
        $refreshExpiresAt = now()->addDays((int) config('identity.refresh_token_ttl_days', 30));
        if ($user->is_temporary && $user->demo_expires_at !== null) {
            if ($user->demo_expires_at->isPast()) {
                throw new IdentityAuthenticationException('Temporary identity has expired.');
            }
            $accessExpiresAt = $user->demo_expires_at->lessThan($accessExpiresAt) ? $user->demo_expires_at->copy() : $accessExpiresAt;
            $refreshExpiresAt = $user->demo_expires_at->lessThan($refreshExpiresAt) ? $user->demo_expires_at->copy() : $refreshExpiresAt;
        }
        $access = $user->createToken('identity:'.$project->slug, $membership->effectivePermissionKeys(), $accessExpiresAt);
        $access->accessToken->forceFill([
            'identity_project_id' => $project->id,
            'identity_client_id' => $client->id,
            'identity_refresh_family_id' => $familyId,
        ])->save();
        $refreshPlain = Str::random(96);
        IdentityRefreshToken::query()->create([
            'family_id' => $familyId,
            'user_id' => $user->id,
            'project_id' => $project->id,
            'client_id' => $client->id,
            'token_hash' => hash('sha256', $refreshPlain),
            'expires_at' => $refreshExpiresAt,
        ]);

        return [
            'token_type' => 'Bearer',
            'access_token' => $access->plainTextToken,
            'access_token_expires_at' => $accessExpiresAt->toIso8601String(),
            'expires_in' => $accessExpiresAt->diffInSeconds(now()),
            'refresh_token' => $refreshPlain,
            'refresh_token_expires_at' => $refreshExpiresAt->toIso8601String(),
        ];
    }

    private function revokeFamily(string $familyId): void
    {
        IdentityRefreshToken::query()->where('family_id', $familyId)->whereNull('revoked_at')->update(['revoked_at' => now()]);
        PersonalAccessToken::query()->where('identity_refresh_family_id', $familyId)->delete();
    }

    private function assertProjectAdmin(string $userId, string $projectId): void
    {
        $user = User::query()->findOrFail($userId);
        if ($user->is_system_admin) {
            return;
        }
        $membership = $this->memberships->findActiveForProjectUser($projectId, $userId);
        if (! $membership || (! $membership->is_admin && ! in_array('identity.project.manage', $membership->effectivePermissionKeys(), true))) {
            throw new IdentityAuthorizationException('Project administrator access is required.');
        }
    }

    private function assertSystemAdmin(string $userId): void
    {
        $user = User::query()->findOrFail($userId);
        if (! $user->is_system_admin) {
            throw new IdentityAuthorizationException('Installation administrator access is required.');
        }
    }

    private function touchProjectMemberships(string $projectId): void
    {
        $this->memberships->touchAuthorizationForProject($projectId);
    }

    /** @return array<string, mixed> */
    private function identityPayload(User $user, IdentityProject $project, IdentityProjectClient $client, IdentityProjectMembership $membership): array
    {
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
            'project' => $this->projectPayload($project),
            'client' => $this->clientPayload($client),
            'membership' => $this->membershipPayload($membership),
        ];
    }

    /** @return array<string, mixed> */
    private function projectPayload(IdentityProject $project): array
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
    private function clientPayload(IdentityProjectClient $client): array
    {
        return ['id' => $client->id, 'project_id' => $client->project_id, 'name' => $client->name, 'secret_prefix' => $client->secret_prefix, 'status' => $client->status, 'last_used_at' => $client->last_used_at?->toIso8601String()];
    }

    /** @return array<string, mixed> */
    private function membershipPayload(IdentityProjectMembership $membership): array
    {
        return [
            'id' => $membership->id,
            'project_id' => $membership->project_id,
            'user' => $membership->relationLoaded('user') ? ['id' => $membership->user->id, 'email' => $membership->user->email, 'username' => $membership->user->username] : ['id' => $membership->user_id],
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
    private function rolePayload(IdentityProjectRole $role): array
    {
        return ['id' => $role->id, 'project_id' => $role->project_id, 'name' => $role->name, 'slug' => $role->slug, 'description' => $role->description, 'permission_ids' => $role->permissions()->pluck('identity_project_permissions.id')->all()];
    }

    /** @return array<string, mixed> */
    private function permissionPayload(IdentityProjectPermission $permission): array
    {
        return ['id' => $permission->id, 'project_id' => $permission->project_id, 'key' => $permission->key, 'name' => $permission->name, 'description' => $permission->description, 'source' => $permission->source, 'source_client_id' => $permission->source_client_id, 'status' => $permission->status];
    }

    /** @return array<string, mixed> */
    private function webhookPayload(IdentityWebhookEndpoint $endpoint): array
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

    private function assertWebhookUrl(string $url): void
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            throw new IdentityAuthorizationException('The webhook URL is invalid.');
        }
        if (app()->environment('production') && $scheme !== 'https') {
            throw new IdentityAuthorizationException('Production webhook URLs must use HTTPS.');
        }
        if (app()->environment('production')) {
            $resolved = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);
            if ($host === 'localhost' || str_ends_with($host, '.localhost')
                || filter_var($resolved, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                throw new IdentityAuthorizationException('Private webhook destinations are not allowed in production.');
            }
        }
    }

    private function issueEmailVerification(User $user): string
    {
        $code = (string) random_int(100000, 999999);
        $user->forceFill([
            'email_verification_code_hash' => $this->secretHash($code),
            'email_verification_expires_at' => now()->addMinutes(
                (int) config('identity.email_verification_ttl_minutes', 15),
            ),
        ])->save();
        $user->notify(new VerifyEmailCode($code));

        return $code;
    }

    private function secretHash(string $value): string
    {
        return hash_hmac('sha256', $value, (string) config('app.key'));
    }

    /** @param array<string, mixed> $metadata */
    private function audit(string $event, ?string $projectId, ?string $clientId, ?string $actorUserId, ?string $targetType, ?string $targetId, array $metadata = [], ?string $ipAddress = null, ?string $userAgent = null): void
    {
        IdentityAuditEvent::query()->create([
            'project_id' => $projectId,
            'client_id' => $clientId,
            'actor_user_id' => $actorUserId,
            'event' => $event,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'metadata' => $metadata ?: null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }
}
