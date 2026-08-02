<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\IdentityMembership;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityMembershipId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use Zolta\Domain\ValueObjects\UserId;

interface IdentityMembershipRepository
{
    public function findForProject(
        IdentityProjectId $projectId,
        IdentityMembershipId $membershipId,
    ): ?IdentityMembership;

    public function findForProjectUser(
        IdentityProjectId $projectId,
        UserId $userId,
    ): ?IdentityMembership;

    public function save(IdentityMembership $membership): void;

    public function delete(IdentityMembership $membership): void;

    public function incrementAuthorizationVersionForProject(IdentityProjectId $projectId): void;
}
