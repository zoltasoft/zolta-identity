<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\UserManagementService\Infrastructure\Mail\Mailable\WelcomeUser;
use App\Services\UserManagementService\Infrastructure\Mail\VerificationEmail;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_verification_code_and_queues_the_welcome_email(): void
    {
        Mail::fake();

        $this->postJson('/api/auth/register', [
            'username' => 'new-user',
            'email' => 'new-user@example.com',
            'password' => 'secure-password',
            'terms' => true,
        ])
            ->assertOk()
            ->assertJsonMissingPath('data.user.role')
            ->assertJsonMissingPath('data.user.role_id')
            ->assertJsonMissingPath('data.user.permissions');

        $user = User::query()->where('email', 'new-user@example.com')->firstOrFail();
        $this->assertMatchesRegularExpression('/^\d{6}$/', (string) $user->verification_code);
        $this->assertNotNull($user->verification_expires_at);
        Mail::assertQueued(
            WelcomeUser::class,
            fn (WelcomeUser $mail): bool => $mail->hasTo($user->email) && $mail->verificationCode === $user->verification_code
        );
    }

    public function test_an_authenticated_user_can_verify_their_email_with_the_current_code(): void
    {
        $user = $this->user('verify', '123456');
        Sanctum::actingAs($user);

        $this->postJson('/api/auth/email/verification', ['code' => '123456'])
            ->assertOk()
            ->assertJsonPath('data.message', 'Your email address has been verified.');

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->verification_code);
        $this->assertNull($user->verification_expires_at);
    }

    public function test_an_invalid_verification_code_returns_field_feedback(): void
    {
        $user = $this->user('invalid-code', '123456');
        Sanctum::actingAs($user);

        $this->postJson('/api/auth/email/verification', ['code' => '654321'])
            ->assertUnprocessable()
            ->assertJsonPath(
                'errors.public.errors.code.0',
                'The verification code is invalid or has expired.'
            );
    }

    public function test_a_user_can_request_a_fresh_verification_code(): void
    {
        Mail::fake();
        $user = $this->user('resend', '123456');
        Sanctum::actingAs($user);

        $this->postJson('/api/auth/email/verification/resend')
            ->assertOk()
            ->assertJsonPath('data.message', 'A new verification code has been sent.')
            ->assertJsonMissingPath('data.development_code');

        $user->refresh();
        $this->assertMatchesRegularExpression('/^\d{6}$/', (string) $user->verification_code);
        $this->assertNotNull($user->verification_expires_at);
        Mail::assertQueued(
            VerificationEmail::class,
            fn (VerificationEmail $mail): bool => $mail->hasTo($user->email) && $mail->code === $user->verification_code
        );
    }

    public function test_local_development_can_receive_the_fresh_verification_code(): void
    {
        config()->set('app.expose_email_verification_code', true);
        Mail::fake();
        $user = $this->user('local-code', '123456');
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/email/verification/resend')
            ->assertOk();

        $user->refresh();
        $response->assertJsonPath('data.development_code', $user->verification_code);
    }

    private function user(string $name, string $code): User
    {
        return User::query()->create([
            'id' => (string) Str::uuid(),
            'username' => $name,
            'email' => "{$name}@example.com",
            'password' => 'password',
            'terms' => 'accepted',
            'verification_code' => $code,
            'verification_expires_at' => now()->addHour(),
        ]);
    }
}
