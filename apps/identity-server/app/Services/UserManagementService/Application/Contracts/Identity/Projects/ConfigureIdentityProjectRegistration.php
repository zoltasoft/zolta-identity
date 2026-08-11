<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Projects;

interface ConfigureIdentityProjectRegistration
{
    public function updateProjectRegistration(
        string $actorUserId,
        string $projectId,
        string $mode,
        ?string $roleId,
        bool $emailVerificationRequired,
    ): void;
}
