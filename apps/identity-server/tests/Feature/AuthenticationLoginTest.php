<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AuthenticationLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_login_with_valid_credentials(): void
    {
        $user = $this->user();

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ])
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', $user->email)
            ->assertJsonMissingPath('data.user.role')
            ->assertJsonMissingPath('data.user.role_id')
            ->assertJsonMissingPath('data.user.permissions')
            ->assertJsonStructure(['data' => ['access_token']]);

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_invalid_credentials_return_field_feedback_instead_of_a_server_error(): void
    {
        $user = $this->user();

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ])
            ->assertUnprocessable()
            ->assertJsonPath(
                'errors.public.errors.credentials',
                'Invalid credentials provided.'
            );

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    private function user(): User
    {
        return User::query()->create([
            'id' => (string) Str::uuid(),
            'username' => 'login-user',
            'email' => 'login-user@example.com',
            'password' => 'correct-password',
            'terms' => 'accepted',
            'email_verified_at' => now(),
        ]);
    }
}
