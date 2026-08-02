<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services;

use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\AcceptIdentityInvitation;
use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\IssueIdentityAccess;
use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\ManageIdentitySessions;
use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\ReadIdentityAccessContext;
use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\ReadIdentitySessions;
use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\RecoverIdentityPassword;
use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\SyncIdentityClientManifest;
use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\VerifyIdentityEmail;
use App\Services\UserManagementService\Application\Exceptions\IdentityAuthenticationException;
use App\Services\UserManagementService\Application\Exceptions\IdentityAuthorizationException;
use App\Services\UserManagementService\Application\Exceptions\IdentityResourceNotFoundException;
use App\Services\UserManagementService\Domain\Aggregates\IdentityMembership as DomainIdentityMembership;
use App\Services\UserManagementService\Domain\Repositories\IdentityMembershipRepository;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityRoleId;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProject;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectInvitation;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityRefreshToken;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\Role;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use App\Services\UserManagementService\Infrastructure\Notifications\ResetIdentityPassword;
use App\Services\UserManagementService\Infrastructure\Notifications\VerifyEmailCode;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityProjectClientRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityProjectMembershipRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityProjectRoleRepository;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityAuditRecorder;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityClientAuthenticator;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityPayloadFactory;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityPermissionManifestSynchronizer;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityTokenManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Zolta\Domain\ValueObjects\UserId;

final readonly class EloquentIdentityAuthenticationService implements AcceptIdentityInvitation, IssueIdentityAccess, ManageIdentitySessions, ReadIdentityAccessContext, ReadIdentitySessions, RecoverIdentityPassword, SyncIdentityClientManifest, VerifyIdentityEmail
{
    public function __construct(
        private EloquentIdentityProjectMembershipRepository $memberships,
        private IdentityMembershipRepository $membershipAggregates,
        private EloquentIdentityProjectRoleRepository $projectRoles,
        private EloquentIdentityProjectClientRepository $projectClients,
        private IdentityClientAuthenticator $clients,
        private IdentityPermissionManifestSynchronizer $permissionManifests,
        private IdentityTokenManager $tokens,
        private IdentityPayloadFactory $payloads,
        private IdentityAuditRecorder $audit,
    ) {}

    public function login(
        array $credentials,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        $client = $this->clients->authenticate(
            (string) $credentials['client_id'],
            (string) $credentials['client_secret'],
        );
        $project = $client->project;

        if ($project->mode === 'sandbox') {
            throw new IdentityAuthorizationException('Sandbox projects only accept temporary sessions.');
        }

        $requestedProject = (string) ($credentials['project'] ?? '');
        if ($requestedProject !== ''
            && ! in_array($requestedProject, [$project->id, $project->slug], true)) {
            throw new IdentityAuthenticationException('Invalid credentials.');
        }

        $user = User::query()
            ->whereRaw('lower(email) = ?', [Str::lower((string) $credentials['email'])])
            ->first();
        $isLocked = $user?->locked
            && ($user->lock_expiry === null || $user->lock_expiry->isFuture());

        if (! $user || $isLocked || ! Hash::check((string) $credentials['password'], $user->password)) {
            $this->audit->record(
                'auth.login_failed',
                $project->id,
                $client->id,
                null,
                null,
                null,
                [],
                $ipAddress,
                $userAgent,
            );
            throw new IdentityAuthenticationException('Invalid credentials.');
        }

        if ($user->locked) {
            $user->forceFill(['locked' => false, 'lock_expiry' => null])->save();
        }

        $membership = $this->memberships->findActiveForProjectUser($project->id, $user->id);
        if (! $membership || $project->status !== 'active') {
            throw new IdentityAuthenticationException('This account does not have access to the project.');
        }

        $tokens = $this->tokens->issuePair($user, $project, $client, $membership);
        $this->audit->record(
            'auth.login_succeeded',
            $project->id,
            $client->id,
            $user->id,
            'user',
            $user->id,
            [],
            $ipAddress,
            $userAgent,
        );

        return $tokens + [
            'identity' => $this->payloads->identity($user, $project, $client, $membership),
        ];
    }

    public function authenticationContext(
        string $clientId,
        string $clientSecret,
        ?string $project = null,
    ): array {
        $client = $this->clients->authenticate($clientId, $clientSecret);

        if ($project !== null
            && $project !== ''
            && ! in_array($project, [$client->project->id, $client->project->slug], true)) {
            throw new IdentityAuthenticationException('Invalid client credentials.');
        }

        return [
            'project' => $this->payloads->project($client->project),
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
            ],
        ];
    }

    public function register(
        array $attributes,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        $client = $this->clients->authenticate(
            (string) $attributes['client_id'],
            (string) $attributes['client_secret'],
        );
        $project = $client->project;

        if ($project->mode === 'sandbox') {
            throw new IdentityAuthorizationException(
                'Sandbox projects do not accept permanent registrations.',
            );
        }

        $requestedProject = (string) ($attributes['project'] ?? '');
        if ($requestedProject !== ''
            && ! in_array($requestedProject, [$project->id, $project->slug], true)) {
            throw new IdentityAuthenticationException('Invalid credentials.');
        }

        if ($project->status !== 'active' || $project->registration_mode !== 'public') {
            throw new IdentityAuthorizationException(
                'Public registration is not enabled for this project.',
            );
        }

        return DB::transaction(function () use (
            $attributes,
            $project,
            $client,
            $ipAddress,
            $userAgent,
        ): array {
            $email = Str::lower((string) $attributes['email']);
            if (User::query()->whereRaw('lower(email) = ?', [$email])->exists()) {
                throw new IdentityAuthenticationException(
                    'An account with this email already exists. Sign in instead.',
                );
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
            $roleIds = [];
            if ($project->registration_role_id !== null
                && $this->projectRoles->existsForProject(
                    $project->id,
                    $project->registration_role_id,
                )) {
                $roleIds[] = IdentityRoleId::fromString($project->registration_role_id);
            }
            $membershipAggregate = DomainIdentityMembership::create(
                IdentityProjectId::fromString($project->id),
                new UserId((string) $user->id),
                roleIds: $roleIds,
            );
            $this->membershipAggregates->save($membershipAggregate);
            $membership = $this->memberships->findForProjectOrFail(
                $project->id,
                $membershipAggregate->id()->toString(),
            );

            $tokens = $this->tokens->issuePair($user, $project, $client, $membership);
            $this->issueEmailVerification($user);
            $this->audit->record(
                'auth.registered',
                $project->id,
                $client->id,
                $user->id,
                'user',
                $user->id,
                [],
                $ipAddress,
                $userAgent,
            );

            return $tokens + [
                'identity' => $this->payloads->identity($user, $project, $client, $membership),
            ];
        });
    }

    public function createSandboxSession(
        string $clientId,
        string $clientSecret,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        $client = $this->clients->authenticate($clientId, $clientSecret);
        $project = $client->project;

        if ($project->mode !== 'sandbox') {
            throw new IdentityAuthorizationException(
                'Temporary sessions are only available for sandbox projects.',
            );
        }

        return DB::transaction(function () use (
            $project,
            $client,
            $ipAddress,
            $userAgent,
        ): array {
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
            $roleIds = [];
            if ($project->registration_role_id !== null
                && $this->projectRoles->existsForProject(
                    $project->id,
                    $project->registration_role_id,
                )) {
                $roleIds[] = IdentityRoleId::fromString($project->registration_role_id);
            }
            $membershipAggregate = DomainIdentityMembership::create(
                IdentityProjectId::fromString($project->id),
                new UserId((string) $user->id),
                roleIds: $roleIds,
            );
            $this->membershipAggregates->save($membershipAggregate);
            $membership = $this->memberships->findForProjectOrFail(
                $project->id,
                $membershipAggregate->id()->toString(),
            );

            $tokens = $this->tokens->issuePair($user, $project, $client, $membership);
            $this->audit->record(
                'auth.sandbox_session_created',
                $project->id,
                $client->id,
                $user->id,
                'user',
                $user->id,
                ['expires_at' => $expiresAt->toIso8601String()],
                $ipAddress,
                $userAgent,
            );

            return $tokens + [
                'is_temporary' => true,
                'expires_at' => $expiresAt->toIso8601String(),
                'identity' => $this->payloads->identity($user, $project, $client, $membership),
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
            || ! hash_equals(
                (string) $user->email_verification_code_hash,
                $this->secretHash($code),
            )) {
            throw new IdentityAuthenticationException(
                'The verification code is invalid or expired.',
            );
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'email_verification_code_hash' => null,
            'email_verification_expires_at' => null,
        ])->save();
        $this->audit->record(
            'auth.email_verified',
            null,
            null,
            $user->id,
            'user',
            $user->id,
        );
    }

    public function requestPasswordReset(
        string $clientId,
        string $clientSecret,
        string $email,
    ): array {
        $this->clients->authenticate($clientId, $clientSecret);
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

    public function resetPassword(
        string $clientId,
        string $clientSecret,
        string $email,
        string $token,
        string $password,
    ): void {
        $this->clients->authenticate($clientId, $clientSecret);
        $user = User::query()->whereRaw('lower(email) = ?', [Str::lower($email)])->first();
        $reset = $user
            ? DB::table('password_reset_tokens')->where('email', $user->email)->first()
            : null;
        $expiresAt = $reset?->created_at
            ? Carbon::parse($reset->created_at)->addMinutes(
                (int) config('identity.password_reset_ttl_minutes', 60),
            )
            : null;

        if (! $user || ! $reset || $expiresAt?->isPast() !== false
            || ! hash_equals((string) $reset->token, $this->secretHash($token))) {
            throw new IdentityAuthenticationException(
                'The password reset token is invalid or expired.',
            );
        }

        DB::transaction(function () use ($user, $password): void {
            $user->forceFill(['password' => $password])->save();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            $this->tokens->revokeUser($user->id);
            $this->audit->record(
                'auth.password_reset',
                null,
                null,
                $user->id,
                'user',
                $user->id,
            );
        });
    }

    public function refresh(
        array $credentials,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        $client = $this->clients->authenticate(
            (string) $credentials['client_id'],
            (string) $credentials['client_secret'],
        );
        $plainRefreshToken = (string) $credentials['refresh_token'];

        $result = DB::transaction(function () use (
            $client,
            $plainRefreshToken,
            $ipAddress,
            $userAgent,
        ): array {
            $refresh = IdentityRefreshToken::query()
                ->where('token_hash', hash('sha256', $plainRefreshToken))
                ->lockForUpdate()
                ->first();

            if (! $refresh || $refresh->client_id !== $client->id) {
                throw new IdentityAuthenticationException('Invalid refresh token.');
            }

            if ($refresh->rotated_to_id !== null || $refresh->used_at !== null) {
                $this->tokens->revokeFamily($refresh->family_id);
                $this->audit->record(
                    'auth.refresh_replay_detected',
                    $refresh->project_id,
                    $refresh->client_id,
                    $refresh->user_id,
                    'session',
                    $refresh->family_id,
                    [],
                    $ipAddress,
                    $userAgent,
                );

                return ['replay_detected' => true];
            }

            if ($refresh->revoked_at !== null || $refresh->expires_at->isPast()) {
                throw new IdentityAuthenticationException('Refresh token is expired or revoked.');
            }

            $user = User::query()->findOrFail($refresh->user_id);
            $project = IdentityProject::query()
                ->where('status', 'active')
                ->findOrFail($refresh->project_id);
            $membership = $this->memberships->findActiveForProjectUser(
                $project->id,
                $user->id,
            );

            if (! $membership) {
                $this->tokens->revokeFamily($refresh->family_id);
                throw new IdentityAuthenticationException('Project access has been revoked.');
            }

            $tokens = $this->tokens->issuePair(
                $user,
                $project,
                $client,
                $membership,
                $refresh->family_id,
            );
            $newRefresh = IdentityRefreshToken::query()
                ->where('token_hash', hash('sha256', $tokens['refresh_token']))
                ->firstOrFail();
            $refresh->forceFill([
                'used_at' => now(),
                'rotated_to_id' => $newRefresh->id,
            ])->save();
            $this->audit->record(
                'auth.token_refreshed',
                $project->id,
                $client->id,
                $user->id,
                'session',
                $refresh->family_id,
                [],
                $ipAddress,
                $userAgent,
            );

            return $tokens + [
                'identity' => $this->payloads->identity($user, $project, $client, $membership),
            ];
        });

        if (($result['replay_detected'] ?? false) === true) {
            throw new IdentityAuthenticationException(
                'Refresh token reuse detected. The session has been revoked.',
            );
        }

        return $result;
    }

    public function introspect(
        string $clientId,
        string $clientSecret,
        string $accessToken,
    ): array {
        $client = $this->clients->authenticate($clientId, $clientSecret);
        $token = PersonalAccessToken::findToken($accessToken);

        if (! $token
            || $token->expires_at?->isPast()
            || $token->identity_project_id !== $client->project_id) {
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
        if (! $user
            || ($user->is_temporary
                && ($user->demo_expires_at === null || $user->demo_expires_at->isPast()))) {
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
            $this->tokens->revokeFamily($token->identity_refresh_family_id);

            return;
        }

        $token->delete();
    }

    public function currentIdentity(string $userId, string $accessToken): array
    {
        $token = PersonalAccessToken::findToken($accessToken);
        if (! $token
            || (string) $token->tokenable_id !== $userId
            || ! $token->identity_project_id) {
            throw new IdentityAuthenticationException('Invalid access token.');
        }

        $user = User::query()->findOrFail($userId);
        $project = IdentityProject::query()->findOrFail($token->identity_project_id);
        $client = $this->projectClients->findForProjectOrFail(
            $project->id,
            (string) $token->identity_client_id,
        );
        $membership = $this->memberships->findActiveForProjectUser($project->id, $userId);

        if ($membership === null) {
            throw new IdentityResourceNotFoundException('Identity project membership');
        }

        return $this->payloads->identity($user, $project, $client, $membership);
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
                    'project' => $project ? $this->payloads->project($project) : null,
                    'client' => $client ? $this->payloads->client($client) : null,
                    'created_at' => $token->created_at?->toIso8601String(),
                    'expires_at' => $token->expires_at->toIso8601String(),
                ];
            })->values()->all();
    }

    public function revokeSession(string $userId, string $familyId): void
    {
        $owned = IdentityRefreshToken::query()
            ->where('user_id', $userId)
            ->where('family_id', $familyId)
            ->exists();

        if (! $owned) {
            throw new IdentityAuthorizationException(
                'The session does not belong to the authenticated user.',
            );
        }

        $this->tokens->revokeFamily($familyId);
        $this->audit->record(
            'auth.session_revoked',
            null,
            null,
            $userId,
            'session',
            $familyId,
        );
    }

    public function acceptInvitation(array $attributes): array
    {
        return DB::transaction(function () use ($attributes): array {
            $invitation = IdentityProjectInvitation::query()
                ->where(
                    'token_hash',
                    hash('sha256', (string) $attributes['invitation_token']),
                )
                ->lockForUpdate()
                ->first();

            if (! $invitation
                || $invitation->accepted_at !== null
                || $invitation->expires_at->isPast()) {
                throw new IdentityAuthenticationException('Invitation is invalid or expired.');
            }

            $user = User::query()
                ->whereRaw('lower(email) = ?', [Str::lower($invitation->email)])
                ->first();

            if (! $user) {
                $user = User::query()->create([
                    'id' => (string) Str::uuid(),
                    'username' => $attributes['username'],
                    'email' => $invitation->email,
                    'password' => $attributes['password'],
                    'role_id' => Role::query()->value('id'),
                    'terms' => 'accepted',
                    'email_verified_at' => now(),
                ]);
            } elseif (! Hash::check((string) $attributes['password'], $user->password)) {
                throw new IdentityAuthenticationException('Invalid credentials.');
            }

            $projectId = IdentityProjectId::fromString($invitation->project_id);
            $userId = new UserId((string) $user->id);
            $membershipAggregate = $this->membershipAggregates->findForProjectUser(
                $projectId,
                $userId,
            ) ?? DomainIdentityMembership::create(
                $projectId,
                $userId,
                (bool) $invitation->is_admin,
            );
            $membershipAggregate->acceptInvitation((bool) $invitation->is_admin);
            $this->membershipAggregates->save($membershipAggregate);
            $membership = $this->memberships->findForProjectOrFail(
                $invitation->project_id,
                $membershipAggregate->id()->toString(),
            );
            $invitation->forceFill(['accepted_at' => now()])->save();
            $this->audit->record(
                'invitation.accepted',
                $invitation->project_id,
                null,
                $user->id,
                'membership',
                $membership->id,
            );

            return [
                'membership_id' => $membership->id,
                'project_id' => $membership->project_id,
                'user_id' => $user->id,
            ];
        });
    }

    public function syncOwnPermissionManifest(
        string $clientId,
        string $clientSecret,
        array $manifest,
    ): array {
        $client = $this->clients->authenticate($clientId, $clientSecret);
        $permissions = $this->permissionManifests->sync(
            $client->project_id,
            $client->id,
            $manifest,
        );
        $this->audit->record(
            'permission_manifest.synced',
            $client->project_id,
            $client->id,
            null,
            'client',
            $client->id,
            ['permission_count' => count($manifest)],
        );

        return $permissions;
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
}
