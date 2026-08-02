<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use App\Services\UserManagementService\Infrastructure\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_requests_do_not_expose_account_existence(): void
    {
        Notification::fake();
        $user = $this->user('known');

        $this->postJson('/api/auth/password/forgot', ['email' => $user->email])
            ->assertOk()
            ->assertJsonPath(
                'data.message',
                'If an account exists for that email, a password reset link has been sent.'
            );

        $this->postJson('/api/auth/password/forgot', ['email' => 'unknown@example.com'])
            ->assertOk()
            ->assertJsonPath(
                'data.message',
                'If an account exists for that email, a password reset link has been sent.'
            );

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_a_valid_token_resets_the_password_and_revokes_existing_tokens(): void
    {
        $user = $this->user('reset');
        $user->createToken('existing-session');
        $token = Password::broker()->createToken($user);

        $this->postJson('/api/auth/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])
            ->assertOk()
            ->assertJsonPath('data.message', 'Your password has been reset.');

        $user->refresh();
        $this->assertTrue(Hash::check('new-secure-password', $user->password));
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_an_invalid_reset_token_returns_field_feedback(): void
    {
        $user = $this->user('invalid');

        $this->postJson('/api/auth/password/reset', [
            'email' => $user->email,
            'token' => 'invalid-token',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])
            ->assertUnprocessable()
            ->assertJsonPath(
                'errors.public.errors.token.0',
                'This password reset link is invalid or has expired.'
            );
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
        ]);
    }
}
