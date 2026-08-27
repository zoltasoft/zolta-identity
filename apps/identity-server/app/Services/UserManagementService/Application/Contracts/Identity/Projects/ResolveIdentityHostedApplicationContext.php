<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Projects;

use App\Services\UserManagementService\Application\DTOs\Output\IdentityHostedApplicationContext;

interface ResolveIdentityHostedApplicationContext
{
    public function resolve(string $applicationKey, bool $sandbox): ?IdentityHostedApplicationContext;
}
