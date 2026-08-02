<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectMembership;
use Zolta\Cqrs\Repositories\Query\RepositoryConstraint;
use Zolta\Cqrs\Repositories\Query\RepositoryQuery;

/** @extends EloquentProjectScopedRepository<IdentityProjectMembership> */
final class EloquentIdentityProjectMembershipRepository extends EloquentProjectScopedRepository
{
    protected array $allowedFilters = ['created_at'];

    protected array $allowedConstraintFields = ['id', 'project_id', 'user_id', 'status'];

    protected array $allowedRelations = ['user', 'roles', 'permissions'];

    protected function modelClass(): string
    {
        return IdentityProjectMembership::class;
    }

    public function findActiveForProjectUser(string $projectId, string $userId): ?IdentityProjectMembership
    {
        $query = RepositoryQuery::fromOptions([])->withConstraints(
            RepositoryConstraint::equals('project_id', $projectId),
            RepositoryConstraint::equals('user_id', $userId),
            RepositoryConstraint::equals('status', 'active'),
        );

        $membership = $this->first($query);

        return $membership instanceof IdentityProjectMembership ? $membership : null;
    }

    public function touchAuthorizationForProject(string $projectId): void
    {
        IdentityProjectMembership::query()
            ->where('project_id', $projectId)
            ->increment('authorization_version');
    }
}
