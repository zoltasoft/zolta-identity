<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\IdentityMembership as DomainIdentityMembership;
use App\Services\UserManagementService\Domain\Repositories\IdentityMembershipRepository;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityMembershipId;
use App\Services\UserManagementService\Domain\ValueObjects\IdentityProjectId;
use App\Services\UserManagementService\Infrastructure\Mappers\IdentityMembershipMapper;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectMembership;
use Illuminate\Support\Facades\DB;
use Zolta\Cqrs\Repositories\BaseRepository;
use Zolta\Cqrs\Repositories\Query\RepositoryConstraint;
use Zolta\Cqrs\Repositories\Query\RepositoryQuery;
use Zolta\Domain\ValueObjects\UserId;

final class EloquentIdentityMembershipRepository extends BaseRepository implements IdentityMembershipRepository
{
    protected array $allowedConstraintFields = ['id', 'project_id', 'user_id'];

    protected bool $enableReadCaching = false;

    protected function modelClass(): string
    {
        return IdentityProjectMembership::class;
    }

    public function findForProject(
        IdentityProjectId $projectId,
        IdentityMembershipId $membershipId,
    ): ?DomainIdentityMembership {
        $model = $this->first(RepositoryQuery::fromOptions([])->withConstraints(
            RepositoryConstraint::equals('id', $membershipId->toString()),
            RepositoryConstraint::equals('project_id', $projectId->toString()),
        ));

        return $model instanceof IdentityProjectMembership ? IdentityMembershipMapper::toDomain($model) : null;
    }

    public function findForProjectUser(
        IdentityProjectId $projectId,
        UserId $userId,
    ): ?DomainIdentityMembership {
        $model = $this->first(RepositoryQuery::fromOptions([])->withConstraints(
            RepositoryConstraint::equals('project_id', $projectId->toString()),
            RepositoryConstraint::equals('user_id', $userId->toString()),
        ));

        return $model instanceof IdentityProjectMembership ? IdentityMembershipMapper::toDomain($model) : null;
    }

    public function save(DomainIdentityMembership $membership): void
    {
        DB::transaction(function () use ($membership): void {
            $existing = $this->show($membership->id()->toString());
            $model = $existing instanceof IdentityProjectMembership ? $existing : new IdentityProjectMembership;
            $model = IdentityMembershipMapper::fill($model, $membership);
            $existing instanceof IdentityProjectMembership ? $this->update($model) : $this->create($model);
            $model->roles()->sync(
                array_map(
                    static fn ($id): string => $id->toString(),
                    $membership->roleIds(),
                ),
            );
            $model->permissions()->sync(
                array_map(
                    static fn ($id): string => $id->toString(),
                    $membership->permissionIds(),
                ),
            );
        });
    }

    public function remove(DomainIdentityMembership $membership): void
    {
        $model = $this->first(RepositoryQuery::fromOptions([])->withConstraints(
            RepositoryConstraint::equals('id', $membership->id()->toString()),
            RepositoryConstraint::equals('project_id', $membership->projectId()->toString()),
        ));
        if ($model instanceof IdentityProjectMembership) {
            parent::delete($model);
        }
    }

    public function incrementAuthorizationVersionForProject(IdentityProjectId $projectId): void
    {
        IdentityProjectMembership::query()
            ->where('project_id', $projectId->toString())
            ->increment('authorization_version');
    }
}
