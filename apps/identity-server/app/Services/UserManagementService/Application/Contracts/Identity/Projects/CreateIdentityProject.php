<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Projects;

interface CreateIdentityProject
{
    /** @return array<string, mixed> */
    public function createProject(
        string $actorUserId,
        array $attributes,
    ): array;
}
