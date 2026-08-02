<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\IdentityProject;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;

interface IdentityProjectRepository
{
    public function find(IdentityProjectId $projectId): ?IdentityProject;

    public function save(IdentityProject $project): void;
}
