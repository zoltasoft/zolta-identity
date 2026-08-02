<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AccountSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_list_their_sessions_and_identify_the_current_one(): void
    {
        $user = $this->user('owner');
        $current = $user->createToken('current-session');
        $other = $user->createToken('other-session');

        $response = $this->withToken($current->plainTextToken)
            ->getJson('/api/auth/sessions')
            ->assertOk();

        $sessions = collect($response->json('data.sessions'));
        $this->assertCount(2, $sessions);
        $this->assertTrue((bool) $sessions->firstWhere('id', (string) $current->accessToken->getKey())['current']);
        $this->assertFalse((bool) $sessions->firstWhere('id', (string) $other->accessToken->getKey())['current']);
    }

    public function test_a_user_can_revoke_an_owned_session_but_not_another_users_session(): void
    {
        $user = $this->user('owner');
        $current = $user->createToken('current-session');
        $other = $user->createToken('other-session');
        $foreign = $this->user('foreign')->createToken('foreign-session');

        $this->withToken($current->plainTextToken)
            ->deleteJson('/api/auth/sessions/'.$other->accessToken->getKey())
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $other->accessToken->getKey()]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $current->accessToken->getKey()]);

        $this->withToken($current->plainTextToken)
            ->deleteJson('/api/auth/sessions/'.$foreign->accessToken->getKey())
            ->assertUnprocessable();

        $this->assertDatabaseHas('personal_access_tokens', ['id' => $foreign->accessToken->getKey()]);
    }

    private function user(string $name): User
    {
        return User::query()->create([
            'id' => (string) Str::uuid(),
            'username' => $name,
            'email' => "{$name}@example.com",
            'password' => 'original-password',
            'role_id' => (string) DB::table('roles')->value('id'),
            'terms' => 'accepted',
            'email_verified_at' => now(),
        ]);
    }
}
