<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\UserManagementService\Infrastructure\Jobs\DeliverIdentityWebhook;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProject;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectClient;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectMembership;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectPermission;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectRole;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityWebhookDelivery;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityWebhookEndpoint;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use App\Services\UserManagementService\Infrastructure\Webhooks\IdentityWebhookPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

final class IdentityAccessServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_resolve_the_project_authentication_experience(): void
    {
        [, $project, $client, $secret] = $this->identityFixture();
        $project->forceFill([
            'mode' => 'sandbox',
            'sandbox_ttl_minutes' => 45,
        ])->save();

        $this->postJson('/api/v1/identity/auth/context', [
            'project' => $project->slug,
            'client_id' => $client->id,
            'client_secret' => $secret,
        ])->assertOk()
            ->assertJsonPath('data.project.id', $project->id)
            ->assertJsonPath('data.project.mode', 'sandbox')
            ->assertJsonPath('data.project.sandbox_ttl_minutes', 45)
            ->assertJsonPath('data.client.id', $client->id);

        $this->postJson('/api/v1/identity/auth/context', [
            'project' => 'another-project',
            'client_id' => $client->id,
            'client_secret' => $secret,
        ])->assertUnauthorized();
    }

    public function test_login_refresh_and_introspection_return_current_project_grants(): void
    {
        [$user, $project, $client, $secret, $membership] = $this->identityFixture();
        $permission = IdentityProjectPermission::query()->create([
            'project_id' => $project->id,
            'key' => 'documents.read',
            'name' => 'Read documents',
            'source' => 'manual',
            'status' => 'active',
        ]);
        $role = IdentityProjectRole::query()->create([
            'project_id' => $project->id,
            'name' => 'Editor',
            'slug' => 'editor',
        ]);
        $role->permissions()->attach($permission);
        $membership->roles()->attach($role);

        $login = $this->login($user, $project, $client, $secret);
        $this->assertSame(['editor'], $login['identity']['membership']['roles']);

        $this->postJson('/api/v1/identity/auth/introspect', [
            'client_id' => $client->id,
            'client_secret' => $secret,
            'token' => $login['access_token'],
        ])->assertOk()
            ->assertJsonPath('active', true)
            ->assertJsonPath('sub', $user->id)
            ->assertJsonPath('email_verified', true)
            ->assertJsonPath('permissions.0', 'documents.read');

        $refresh = $this->postJson('/api/v1/identity/auth/refresh', [
            'client_id' => $client->id,
            'client_secret' => $secret,
            'refresh_token' => $login['refresh_token'],
        ])->assertOk()->json('data');
        $this->assertNotSame($login['refresh_token'], $refresh['refresh_token']);
    }

    public function test_refresh_token_replay_revokes_the_token_family(): void
    {
        [$user, $project, $client, $secret] = $this->identityFixture();
        $login = $this->login($user, $project, $client, $secret);
        $rotated = $this->postJson('/api/v1/identity/auth/refresh', [
            'client_id' => $client->id,
            'client_secret' => $secret,
            'refresh_token' => $login['refresh_token'],
        ])->assertOk()->json('data');

        $this->postJson('/api/v1/identity/auth/refresh', [
            'client_id' => $client->id,
            'client_secret' => $secret,
            'refresh_token' => $login['refresh_token'],
        ])->assertUnauthorized();
        $this->postJson('/api/v1/identity/auth/introspect', [
            'client_id' => $client->id,
            'client_secret' => $secret,
            'token' => $rotated['access_token'],
        ])->assertOk()->assertJsonPath('active', false);
    }

    public function test_public_project_registration_assigns_the_default_role(): void
    {
        [, $project, $client, $secret] = $this->identityFixture();
        $role = IdentityProjectRole::query()->create([
            'project_id' => $project->id,
            'name' => 'Member',
            'slug' => 'member',
        ]);
        $project->forceFill([
            'registration_mode' => 'public',
            'registration_role_id' => $role->id,
        ])->save();

        $this->postJson('/api/v1/identity/auth/register', [
            'project' => $project->slug,
            'client_id' => $client->id,
            'client_secret' => $secret,
            'username' => 'New member',
            'email' => 'new-member@example.com',
            'password' => 'a-strong-password',
            'password_confirmation' => 'a-strong-password',
        ])->assertCreated()
            ->assertJsonPath('data.identity.membership.roles.0', 'member')
            ->assertJsonPath('data.identity.user.email_verified', false);
    }

    public function test_invite_only_project_rejects_public_registration(): void
    {
        [, $project, $client, $secret] = $this->identityFixture();
        $this->postJson('/api/v1/identity/auth/register', [
            'project' => $project->slug,
            'client_id' => $client->id,
            'client_secret' => $secret,
            'username' => 'No access',
            'email' => 'no-access@example.com',
            'password' => 'a-strong-password',
            'password_confirmation' => 'a-strong-password',
        ])->assertForbidden();
    }

    public function test_sandbox_client_creates_a_temporary_identity_without_credentials(): void
    {
        [, $project, $client, $secret] = $this->identityFixture();
        $role = IdentityProjectRole::query()->create([
            'project_id' => $project->id,
            'name' => 'Demo member',
            'slug' => 'demo-member',
        ]);
        $project->forceFill([
            'mode' => 'sandbox',
            'sandbox_ttl_minutes' => 60,
            'registration_role_id' => $role->id,
        ])->save();

        $session = $this->postJson('/api/v1/identity/auth/sandbox-session', [
            'client_id' => $client->id,
            'client_secret' => $secret,
        ])->assertCreated()
            ->assertJsonPath('data.is_temporary', true)
            ->assertJsonPath('data.identity.project.mode', 'sandbox')
            ->assertJsonPath('data.identity.membership.roles.0', 'demo-member')
            ->json('data');

        $this->postJson('/api/v1/identity/auth/introspect', [
            'client_id' => $client->id,
            'client_secret' => $secret,
            'token' => $session['access_token'],
        ])->assertOk()
            ->assertJsonPath('active', true)
            ->assertJsonPath('project_slug', $project->slug)
            ->assertJsonPath('project_mode', 'sandbox')
            ->assertJsonPath('is_temporary', true);

        $temporary = User::query()->findOrFail($session['identity']['user']['id']);
        $this->assertTrue($temporary->is_temporary);
        $this->assertNotNull($temporary->email_verified_at);
        $this->assertEqualsWithDelta(now()->addHour()->getTimestamp(), $temporary->demo_expires_at?->getTimestamp(), 5);
    }

    public function test_live_project_rejects_sandbox_sessions(): void
    {
        [, , $client, $secret] = $this->identityFixture();

        $this->postJson('/api/v1/identity/auth/sandbox-session', [
            'client_id' => $client->id,
            'client_secret' => $secret,
        ])->assertForbidden();
    }

    public function test_identity_cleanup_webhooks_are_queued_for_subscribed_project_endpoints(): void
    {
        Queue::fake();
        [, $project] = $this->identityFixture();
        IdentityWebhookEndpoint::query()->create([
            'project_id' => $project->id,
            'url' => 'http://localhost:8000/api/webhooks/identity',
            'events' => ['identity.user.expired'],
            'secret' => Str::random(64),
            'secret_prefix' => 'test',
            'status' => 'active',
        ]);
        $userId = (string) Str::uuid();

        app(IdentityWebhookPublisher::class)->publish($project->id, 'identity.user.expired', [
            'user_id' => $userId,
        ]);

        $delivery = IdentityWebhookDelivery::query()->firstOrFail();
        $this->assertSame('identity.user.expired', $delivery->event);
        $this->assertSame($userId, $delivery->payload['data']['user_id']);
        Queue::assertPushed(DeliverIdentityWebhook::class);
    }

    public function test_registered_user_can_resend_and_verify_email(): void
    {
        config()->set('identity.expose_development_tokens', true);
        [, $project, $client, $secret] = $this->identityFixture();
        $project->forceFill(['registration_mode' => 'public'])->save();
        $registration = $this->postJson('/api/v1/identity/auth/register', [
            'project' => $project->slug,
            'client_id' => $client->id,
            'client_secret' => $secret,
            'username' => 'Verify me',
            'email' => 'verify-me@example.com',
            'password' => 'a-strong-password',
            'password_confirmation' => 'a-strong-password',
        ])->assertCreated()->json('data');

        $code = $this->withToken($registration['access_token'])
            ->postJson('/api/v1/identity/auth/email/verification/resend')
            ->assertOk()
            ->json('data.development_code');
        $this->withToken($registration['access_token'])
            ->postJson('/api/v1/identity/auth/email/verification', ['code' => $code])
            ->assertOk();
        $this->assertNotNull(User::query()->where('email', 'verify-me@example.com')->value('email_verified_at'));
    }

    public function test_password_reset_revokes_existing_sessions(): void
    {
        config()->set('identity.expose_development_tokens', true);
        [$user, $project, $client, $secret] = $this->identityFixture();
        $login = $this->login($user, $project, $client, $secret);
        $token = $this->postJson('/api/v1/identity/auth/password/forgot', [
            'client_id' => $client->id,
            'client_secret' => $secret,
            'email' => $user->email,
        ])->assertOk()->json('data.development_token');

        $this->postJson('/api/v1/identity/auth/password/reset', [
            'client_id' => $client->id,
            'client_secret' => $secret,
            'email' => $user->email,
            'token' => $token,
            'password' => 'replacement-password',
            'password_confirmation' => 'replacement-password',
        ])->assertOk();
        $this->postJson('/api/v1/identity/auth/introspect', [
            'client_id' => $client->id,
            'client_secret' => $secret,
            'token' => $login['access_token'],
        ])->assertOk()->assertJsonPath('active', false);
        $this->postJson('/api/v1/identity/auth/login', [
            'project' => $project->slug,
            'client_id' => $client->id,
            'client_secret' => $secret,
            'email' => $user->email,
            'password' => 'replacement-password',
        ])->assertOk();
    }

    public function test_bootstrap_creates_owner_console_project_and_client(): void
    {
        $this->artisan('identity:bootstrap', [
            'email' => 'owner@example.com',
            '--name' => 'Owner',
            '--password' => 'strong-password-123',
        ])->assertSuccessful();

        $this->assertDatabaseHas('users', ['email' => 'owner@example.com', 'is_system_admin' => true]);
        $this->assertDatabaseHas('identity_projects', [
            'slug' => 'identity-console',
            'registration_mode' => 'invite_only',
        ]);
        $this->assertDatabaseCount('identity_project_clients', 1);
    }

    public function test_client_from_another_project_cannot_introspect_a_token(): void
    {
        [$user, $project, $client, $secret] = $this->identityFixture();
        $login = $this->login($user, $project, $client, $secret);
        [, , $otherClient, $otherSecret] = $this->identityFixture();

        $this->postJson('/api/v1/identity/auth/introspect', [
            'client_id' => $otherClient->id,
            'client_secret' => $otherSecret,
            'token' => $login['access_token'],
        ])->assertOk()->assertJsonPath('active', false);
    }

    public function test_project_administrator_only_lists_and_reads_their_projects_resources(): void
    {
        [$administrator, $project, $client, $secret] = $this->identityFixture(true);
        [, $otherProject] = $this->identityFixture();
        IdentityProjectRole::query()->create([
            'project_id' => $otherProject->id,
            'name' => 'Other project role',
            'slug' => 'other-project-role',
        ]);
        IdentityProjectPermission::query()->create([
            'project_id' => $otherProject->id,
            'key' => 'other.read',
            'name' => 'Other project permission',
            'source' => 'manual',
            'status' => 'active',
        ]);
        IdentityWebhookEndpoint::query()->create([
            'project_id' => $otherProject->id,
            'url' => 'https://other.example.com/identity',
            'events' => ['identity.user.expired'],
            'secret' => Str::random(64),
            'secret_prefix' => 'other',
            'status' => 'active',
        ]);
        $accessToken = $this->login($administrator, $project, $client, $secret)['access_token'];

        $this->withToken($accessToken)
            ->getJson('/api/v1/identity/projects')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $project->id);

        $this->withToken($accessToken)
            ->getJson("/api/v1/identity/projects/{$project->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data.clients')
            ->assertJsonCount(1, 'data.memberships')
            ->assertJsonCount(0, 'data.roles')
            ->assertJsonCount(0, 'data.permissions')
            ->assertJsonCount(0, 'data.webhooks');

        $this->withToken($accessToken)
            ->getJson("/api/v1/identity/projects/{$otherProject->id}")
            ->assertForbidden();
    }

    public function test_project_lifecycle_writes_use_the_explicit_project_domain_commands(): void
    {
        [$administrator, $project, $client, $secret] = $this->identityFixture(true);
        $administrator->forceFill(['is_system_admin' => true])->save();
        $accessToken = $this->login($administrator, $project, $client, $secret)['access_token'];

        $createdProjectId = $this->withToken($accessToken)
            ->postJson('/api/v1/identity/projects', [
                'name' => 'New Identity Project',
                'slug' => 'new-identity-project',
                'description' => 'Created through the project aggregate.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.mode', 'live')
            ->assertJsonPath('data.registration_mode', 'invite_only')
            ->json('data.id');

        $this->withToken($accessToken)
            ->patchJson("/api/v1/identity/projects/{$createdProjectId}/registration", [
                'registration_mode' => 'public',
                'registration_role_id' => null,
            ])
            ->assertOk();

        $this->withToken($accessToken)
            ->patchJson("/api/v1/identity/projects/{$createdProjectId}/environment", [
                'mode' => 'sandbox',
                'sandbox_ttl_minutes' => 120,
            ])
            ->assertOk();

        $created = IdentityProject::query()->findOrFail($createdProjectId);
        $this->assertSame('public', $created->registration_mode);
        $this->assertSame('sandbox', $created->mode);
        $this->assertSame(120, $created->sandbox_ttl_minutes);
        $this->assertDatabaseHas('identity_project_memberships', [
            'project_id' => $createdProjectId,
            'user_id' => $administrator->id,
            'is_admin' => true,
        ]);
    }

    public function test_client_and_webhook_lifecycle_writes_preserve_the_public_api_contract(): void
    {
        [$administrator, $project, $client, $secret] = $this->identityFixture(true);
        $accessToken = $this->login($administrator, $project, $client, $secret)['access_token'];

        $createdClient = $this->withToken($accessToken)
            ->postJson("/api/v1/identity/projects/{$project->id}/clients", [
                'name' => 'Job Tracker API',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'active')
            ->json('data');
        $this->assertNotEmpty($createdClient['client_secret']);
        $this->assertSame(
            substr($createdClient['client_secret'], 0, 8),
            $createdClient['secret_prefix'],
        );

        $rotatedClientSecret = $this->withToken($accessToken)
            ->postJson(
                "/api/v1/identity/projects/{$project->id}/clients/{$createdClient['id']}/rotate-secret",
            )
            ->assertOk()
            ->json('data.client_secret');
        $this->assertNotSame($createdClient['client_secret'], $rotatedClientSecret);

        $this->withToken($accessToken)
            ->patchJson(
                "/api/v1/identity/projects/{$project->id}/clients/{$createdClient['id']}",
                ['status' => 'disabled'],
            )
            ->assertOk();
        $this->assertDatabaseHas('identity_project_clients', [
            'id' => $createdClient['id'],
            'project_id' => $project->id,
            'status' => 'disabled',
        ]);

        $createdWebhook = $this->withToken($accessToken)
            ->postJson("/api/v1/identity/projects/{$project->id}/webhooks", [
                'url' => 'https://job-tracker.example.com/webhooks/identity',
                'events' => ['identity.user.expired'],
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'active')
            ->json('data');
        $this->assertNotEmpty($createdWebhook['secret']);
        $this->assertSame(
            substr($createdWebhook['secret'], 0, 8),
            $createdWebhook['secret_prefix'],
        );

        $this->withToken($accessToken)
            ->putJson(
                "/api/v1/identity/projects/{$project->id}/webhooks/{$createdWebhook['id']}",
                [
                    'url' => 'https://job-tracker.example.com/webhooks/accounts',
                    'events' => ['identity.user.deletion_requested'],
                    'status' => 'disabled',
                ],
            )
            ->assertOk();

        $rotatedWebhookSecret = $this->withToken($accessToken)
            ->postJson(
                "/api/v1/identity/projects/{$project->id}/webhooks/{$createdWebhook['id']}/rotate-secret",
            )
            ->assertOk()
            ->json('data.secret');
        $this->assertNotSame($createdWebhook['secret'], $rotatedWebhookSecret);
        $this->assertDatabaseHas('identity_webhook_endpoints', [
            'id' => $createdWebhook['id'],
            'project_id' => $project->id,
            'url' => 'https://job-tracker.example.com/webhooks/accounts',
            'status' => 'disabled',
        ]);

        $this->withToken($accessToken)
            ->deleteJson(
                "/api/v1/identity/projects/{$project->id}/webhooks/{$createdWebhook['id']}",
            )
            ->assertOk();
        $this->assertDatabaseMissing('identity_webhook_endpoints', [
            'id' => $createdWebhook['id'],
        ]);
    }

    public function test_role_permission_and_membership_writes_preserve_the_public_api_contract(): void
    {
        [$administrator, $project, $client, $secret] = $this->identityFixture(true);
        $accessToken = $this->login($administrator, $project, $client, $secret)['access_token'];

        $role = $this->withToken($accessToken)
            ->postJson("/api/v1/identity/projects/{$project->id}/roles", [
                'name' => 'Document editor',
                'slug' => 'document-editor',
                'description' => 'Creates and edits project documents.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.permission_ids', [])
            ->json('data');

        $permission = $this->withToken($accessToken)
            ->postJson("/api/v1/identity/projects/{$project->id}/permissions", [
                'key' => 'documents.write',
                'name' => 'Write documents',
            ])
            ->assertCreated()
            ->assertJsonPath('data.source', 'manual')
            ->assertJsonPath('data.status', 'active')
            ->json('data');

        $this->withToken($accessToken)
            ->putJson(
                "/api/v1/identity/projects/{$project->id}/roles/{$role['id']}/permissions",
                ['permission_ids' => [$permission['id']]],
            )
            ->assertOk();
        $this->assertDatabaseHas('identity_project_role_permission', [
            'role_id' => $role['id'],
            'permission_id' => $permission['id'],
        ]);

        $member = User::query()->create([
            'username' => 'project-member',
            'email' => Str::uuid().'@example.com',
            'password' => 'correct-password',
            'email_verified_at' => now(),
        ]);
        $membership = IdentityProjectMembership::query()->create([
            'project_id' => $project->id,
            'user_id' => $member->id,
            'status' => 'active',
            'is_admin' => false,
        ]);

        $this->withToken($accessToken)
            ->putJson(
                "/api/v1/identity/projects/{$project->id}/memberships/{$membership->id}/access",
                [
                    'role_ids' => [$role['id']],
                    'permission_ids' => [$permission['id']],
                    'is_admin' => false,
                    'status' => 'active',
                ],
            )
            ->assertOk();
        $this->assertDatabaseHas('identity_project_memberships', [
            'id' => $membership->id,
            'authorization_version' => 2,
        ]);
        $this->assertDatabaseHas('identity_membership_role', [
            'membership_id' => $membership->id,
            'role_id' => $role['id'],
        ]);
        $this->assertDatabaseHas('identity_membership_permission', [
            'membership_id' => $membership->id,
            'permission_id' => $permission['id'],
        ]);

        $this->withToken($accessToken)
            ->deleteJson(
                "/api/v1/identity/projects/{$project->id}/memberships/{$membership->id}",
            )
            ->assertOk();
        $this->assertDatabaseMissing('identity_project_memberships', [
            'id' => $membership->id,
        ]);
    }

    public function test_permission_manifest_sync_reactivates_current_and_stales_removed_permissions(): void
    {
        [, $project, $client, $secret, $membership] = $this->identityFixture();

        $this->putJson('/api/v1/identity/clients/permission-manifest', [
            'client_id' => $client->id,
            'client_secret' => $secret,
            'permissions' => [
                ['key' => 'documents.read', 'name' => 'Read documents'],
                ['key' => 'documents.write', 'name' => 'Write documents'],
            ],
        ])->assertOk()
            ->assertJsonCount(2, 'data');

        $this->putJson('/api/v1/identity/clients/permission-manifest', [
            'client_id' => $client->id,
            'client_secret' => $secret,
            'permissions' => [
                ['key' => 'documents.read', 'name' => 'Read project documents'],
            ],
        ])->assertOk()
            ->assertJsonCount(2, 'data');

        $this->assertDatabaseHas('identity_project_permissions', [
            'project_id' => $project->id,
            'source_client_id' => $client->id,
            'key' => 'documents.read',
            'name' => 'Read project documents',
            'source' => 'manifest',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('identity_project_permissions', [
            'project_id' => $project->id,
            'source_client_id' => $client->id,
            'key' => 'documents.write',
            'status' => 'stale',
        ]);
        $this->assertSame(3, $membership->fresh()->authorization_version);
    }

    public function test_project_administrator_cannot_mutate_resources_from_another_project(): void
    {
        [$administrator, $project, $client, $secret, $membership] = $this->identityFixture(true);
        [, $otherProject, $otherClient, , $otherMembership] = $this->identityFixture();
        $role = IdentityProjectRole::query()->create([
            'project_id' => $project->id,
            'name' => 'Project role',
            'slug' => 'project-role',
        ]);
        $otherPermission = IdentityProjectPermission::query()->create([
            'project_id' => $otherProject->id,
            'key' => 'other.manage',
            'name' => 'Other project permission',
            'source' => 'manual',
            'status' => 'active',
        ]);
        $otherRole = IdentityProjectRole::query()->create([
            'project_id' => $otherProject->id,
            'name' => 'Other project role',
            'slug' => 'other-project-role',
        ]);
        $otherWebhook = IdentityWebhookEndpoint::query()->create([
            'project_id' => $otherProject->id,
            'url' => 'https://other.example.com/identity',
            'events' => ['identity.user.expired'],
            'secret' => Str::random(64),
            'secret_prefix' => 'other',
            'status' => 'active',
        ]);
        $accessToken = $this->login($administrator, $project, $client, $secret)['access_token'];

        $this->withToken($accessToken)
            ->postJson("/api/v1/identity/projects/{$project->id}/clients/{$otherClient->id}/rotate-secret")
            ->assertNotFound();

        $this->withToken($accessToken)
            ->putJson("/api/v1/identity/projects/{$project->id}/roles/{$otherRole->id}/permissions", [
                'permission_ids' => [$otherPermission->id],
            ])
            ->assertNotFound();

        $this->withToken($accessToken)
            ->putJson("/api/v1/identity/projects/{$project->id}/roles/{$role->id}/permissions", [
                'permission_ids' => [$otherPermission->id],
            ])
            ->assertForbidden();

        $this->withToken($accessToken)
            ->putJson("/api/v1/identity/projects/{$project->id}/memberships/{$otherMembership->id}/access", [
                'role_ids' => [],
                'permission_ids' => [],
                'is_admin' => false,
                'status' => 'active',
            ])
            ->assertNotFound();

        $this->withToken($accessToken)
            ->putJson("/api/v1/identity/projects/{$project->id}/memberships/{$membership->id}/access", [
                'role_ids' => [$otherRole->id],
                'permission_ids' => [$otherPermission->id],
                'is_admin' => true,
                'status' => 'active',
            ])
            ->assertForbidden();

        $this->withToken($accessToken)
            ->postJson("/api/v1/identity/projects/{$project->id}/webhooks/{$otherWebhook->id}/rotate-secret")
            ->assertNotFound();

        $this->assertSame($otherProject->id, $otherClient->fresh()->project_id);
        $this->assertSame($otherProject->id, $otherRole->fresh()->project_id);
        $this->assertSame($otherProject->id, $otherMembership->fresh()->project_id);
        $this->assertSame($otherProject->id, $otherWebhook->fresh()->project_id);
    }

    /** @return array{User, IdentityProject, IdentityProjectClient, string, IdentityProjectMembership} */
    private function identityFixture(bool $isAdmin = false): array
    {
        $user = User::query()->create([
            'username' => 'identity-user',
            'email' => Str::uuid().'@example.com',
            'password' => 'correct-password',
            'email_verified_at' => now(),
        ]);
        $project = IdentityProject::query()->create([
            'name' => 'Portfolio',
            'slug' => 'portfolio-'.Str::random(6),
            'status' => 'active',
            'mode' => 'live',
            'sandbox_ttl_minutes' => 60,
            'registration_mode' => 'invite_only',
        ]);
        $secret = Str::random(64);
        $client = IdentityProjectClient::query()->create([
            'project_id' => $project->id,
            'name' => 'Portfolio BFF',
            'secret_hash' => hash('sha256', $secret),
            'secret_prefix' => Str::substr($secret, 0, 8),
            'status' => 'active',
        ]);
        $membership = IdentityProjectMembership::query()->create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'status' => 'active',
            'is_admin' => $isAdmin,
        ]);

        return [$user, $project, $client, $secret, $membership];
    }

    /** @return array<string, mixed> */
    private function login(User $user, IdentityProject $project, IdentityProjectClient $client, string $secret): array
    {
        return $this->postJson('/api/v1/identity/auth/login', [
            'project' => $project->slug,
            'client_id' => $client->id,
            'client_secret' => $secret,
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertOk()->json('data');
    }
}
