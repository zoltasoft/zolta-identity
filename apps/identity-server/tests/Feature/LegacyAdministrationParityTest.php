<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class LegacyAdministrationParityTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_administrator_can_manage_global_users_roles_and_permissions(): void
    {
        $administrator = $this->user('administrator', true);
        $managedUser = $this->user('managed-user');
        $token = $administrator->createToken('admin-console')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/users')
            ->assertOk();

        $this->withToken($token)
            ->postJson('/api/permissions', [
                'name' => 'identity.users.audit',
                'description' => 'Audit global identity users',
            ])
            ->assertCreated();

        $permissionId = (string) DB::table('permissions')
            ->where('name', 'identity.users.audit')
            ->value('id');

        $this->withToken($token)
            ->postJson('/api/roles', [
                'name' => 'Identity Auditor',
                'description' => 'Read-only identity auditing role',
                'permission_ids' => [$permissionId],
            ])
            ->assertCreated();

        $roleId = (string) DB::table('roles')
            ->where('role', 'Identity Auditor')
            ->value('id');

        $this->withToken($token)
            ->postJson("/api/roles/{$roleId}/users/{$managedUser->id}")
            ->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $managedUser->id,
            'role_id' => $roleId,
        ]);
        $this->assertDatabaseHas('permission_role', [
            'permission_id' => $permissionId,
            'role_id' => $roleId,
        ]);
    }

    public function test_regular_user_cannot_access_global_administration_routes(): void
    {
        $token = $this->user('regular-user')->createToken('browser')->plainTextToken;

        $this->withToken($token)->getJson('/api/users')->assertForbidden();
        $this->withToken($token)->getJson('/api/roles')->assertForbidden();
        $this->withToken($token)->getJson('/api/permissions')->assertForbidden();
    }

    public function test_regular_user_can_still_update_their_own_account_profile_and_security(): void
    {
        $user = $this->user('account-owner');
        $token = $user->createToken('browser')->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/users/profile', [
                'username' => 'updated-owner',
                'email' => $user->email,
                'avatar_url' => 'https://example.com/avatar.png',
            ])
            ->assertOk();

        $this->withToken($token)
            ->putJson('/api/users/profile/security', [
                'two_factor_enabled' => true,
                'login_alerts_enabled' => true,
                'backup_email' => 'backup@example.com',
            ])
            ->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => 'updated-owner',
            'two_factor_enabled' => true,
            'backup_email' => 'backup@example.com',
        ]);
    }

    private function user(string $name, bool $systemAdministrator = false): User
    {
        return User::query()->create([
            'id' => (string) Str::uuid(),
            'username' => $name,
            'email' => "{$name}@example.com",
            'password' => 'correct-password',
            'role_id' => (string) DB::table('roles')->where('role', 'User')->value('id'),
            'terms' => 'accepted',
            'email_verified_at' => now(),
            'is_system_admin' => $systemAdministrator,
        ]);
    }
}
