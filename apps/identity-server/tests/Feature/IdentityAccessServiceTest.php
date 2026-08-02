<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProject;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectClient;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectMembership;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectPermission;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectRole;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class IdentityAccessServiceTest extends TestCase
{
    use RefreshDatabase;

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
