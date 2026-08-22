<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class LegacyAdministrationParityTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_administrator_can_manage_installation_users(): void
    {
        $administrator = $this->user('administrator', true);
        $managedUser = $this->user('managed-user');
        $token = $administrator->createToken('admin-console')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/users')
            ->assertOk();

        $this->withToken($token)
            ->deleteJson("/api/users/{$managedUser->id}")
            ->assertOk();
    }

    public function test_regular_user_cannot_access_installation_user_administration(): void
    {
        $token = $this->user('regular-user')->createToken('browser')->plainTextToken;

        $this->withToken($token)->getJson('/api/users')->assertForbidden();
    }

    public function test_legacy_global_authorization_endpoints_are_not_registered(): void
    {
        $token = $this->user('administrator', true)->createToken('admin-console')->plainTextToken;

        $this->withToken($token)->getJson('/api/roles')->assertNotFound();
        $this->withToken($token)->getJson('/api/permissions')->assertNotFound();
        $this->withToken($token)->postJson('/api/users/provision-access')->assertNotFound();
    }

    public function test_regular_user_can_still_update_their_own_account_profile_and_security(): void
    {
        $user = $this->user('account-owner');
        $otherUser = $this->user('other-account');
        $token = $user->createToken('browser')->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/users/profile', [
                'user_id' => $otherUser->id,
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
        $this->assertSame('other-account', $otherUser->fresh()->username);
    }

    private function user(string $name, bool $systemAdministrator = false): User
    {
        return User::query()->create([
            'id' => (string) Str::uuid(),
            'username' => $name,
            'email' => "{$name}@example.com",
            'password' => 'correct-password',
            'terms' => 'accepted',
            'email_verified_at' => now(),
            'is_system_admin' => $systemAdministrator,
        ]);
    }
}
