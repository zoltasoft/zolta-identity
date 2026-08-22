<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

final readonly class IdentityHostedApplicationContext
{
    /** @param array<string, mixed> $authentication */
    public function __construct(
        public string $applicationId,
        public string $clientId,
        public string $callbackUrl,
        public array $authentication,
    ) {}
}
