<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\IdentityClient;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityClientId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;

interface IdentityClientRepository
{
    public function findForProject(
        IdentityProjectId $projectId,
        IdentityClientId $clientId,
    ): ?IdentityClient;

    public function save(IdentityClient $client): void;

    public function delete(IdentityClient $client): void;
}
