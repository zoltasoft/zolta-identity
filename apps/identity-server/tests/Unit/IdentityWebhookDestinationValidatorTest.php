<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\UserManagementService\Application\Exceptions\IdentityAuthorizationException;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityWebhookDestinationValidator;
use Tests\TestCase;

final class IdentityWebhookDestinationValidatorTest extends TestCase
{
    public function test_production_rejects_private_webhook_destinations(): void
    {
        $environment = app()->environment();
        app()->instance('env', 'production');

        try {
            $validator = app(IdentityWebhookDestinationValidator::class);

            $this->expectException(IdentityAuthorizationException::class);
            $validator->assertValid('https://127.0.0.1/internal');
        } finally {
            app()->instance('env', $environment);
        }
    }

    public function test_production_accepts_a_public_https_literal(): void
    {
        $environment = app()->environment();
        app()->instance('env', 'production');

        try {
            app(IdentityWebhookDestinationValidator::class)
                ->assertValid('https://8.8.8.8/identity');

            $this->assertTrue(true);
        } finally {
            app()->instance('env', $environment);
        }
    }
}
