<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Projects;

interface ResolveIdentityHostedApplications
{
    /** @return array<string, mixed> */
    public function resolveHostedApplication(string $key): array;

    /** @return array<string, mixed> */
    public function resolveHostedApplicationByClient(string $clientId): array;
}
