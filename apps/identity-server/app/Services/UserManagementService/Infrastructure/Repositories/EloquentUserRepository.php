<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\User as DomainUser;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use App\Services\UserManagementService\Infrastructure\Mappers\UserMapper;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User as EloquentUser;
use Zolta\Cqrs\Laravel\Eloquent\Filters\DateRangeFilter;
use Zolta\Cqrs\Laravel\Eloquent\Filters\SearchFilter;
use Zolta\Cqrs\Repositories\BaseRepository;
use Zolta\Cqrs\Repositories\Query\RepositoryQuery;
use Zolta\Domain\Repositories\Query\AbstractQueryOptions;
use Zolta\Domain\ValueObjects\AccessToken;
use Zolta\Domain\ValueObjects\Email;
use Zolta\Domain\ValueObjects\Pagination;
use Zolta\Domain\ValueObjects\UserId;

/**
 * Eloquent implementation of UserRepository with advanced filters and streaming support.
 */
class EloquentUserRepository extends BaseRepository implements UserRepository
{
    protected int $cacheTtlSeconds = 300;

    protected bool $enableReadCaching = true;

    protected bool $useTaggedCache = true;

    protected string $cacheTag = 'users';

    protected array $allowedFilters = [
        'email',
        'username',
        'name',
        'id',
        'status',
        'created_at',
        'updated_at',
        'last_login_at',
        'email_verified_at',
    ];

    protected array $filterableRelations = [];

    protected array $allowedRelations = ['socialAccounts'];

    protected array $filterOperators = [
        'eq' => '=',
        'ne' => '!=',
        'gt' => '>',
        'gte' => '>=',
        'lt' => '<',
        'lte' => '<=',
        'like' => 'like',
        'not_like' => 'not like',
        'in' => 'in',
        'not_in' => 'not in',
        'null' => 'null',
        'not_null' => 'not null',
        'between' => 'between',
        'date' => 'date',
    ];

    protected function modelClass(): string
    {
        return EloquentUser::class;
    }

    /**
     * Return all users (supports streaming via context.stream).
     */
    public function getAllUsers(AbstractQueryOptions $options): iterable
    {
        $query = $this->query($options);
        foreach ($this->all($query) as $model) {
            yield UserMapper::toDomain($model);
        }
    }

    /**
     * Paginated find all users mapped to domain Pagination value object.
     */
    public function findAllUsers(AbstractQueryOptions $options): Pagination
    {
        $pagination = $this->paginate($options);
        $items = array_map(fn ($model) => UserMapper::toDomain($model), $pagination->items);

        return new Pagination(
            items: $items,
            total: $pagination->total,
            perPage: $pagination->perPage,
            currentPage: $pagination->currentPage,
            lastPage: $pagination->lastPage,
        );
    }

    /**
     * Find a user by id. If additional filters are present they are applied.
     */
    public function findUserById(UserId $id, ?AbstractQueryOptions $options = null): ?DomainUser
    {
        $query = $this->repositoryQuery($options);
        $include = $query->includes();
        $filters = $query->filters();
        $resolvedId = $this->resolveId($id);

        // No extra filters -> use cached show()
        if (empty($filters)) {
            $model = $this->show($resolvedId, $include);

            return $model ? UserMapper::toDomain($model) : null;
        }

        $filters['id'] = $resolvedId;
        $effectiveQuery = RepositoryQuery::fromOptions([
            'filters' => $filters,
            'include' => $include,
            'sort' => $query->sort(),
            'context' => $query->context(),
        ]);
        $result = $this->first($effectiveQuery);

        return $result ? UserMapper::toDomain($result) : null;
    }

    /**
     * Find by email.
     */
    public function findUserByEmail(Email $email, ?AbstractQueryOptions $options = null): ?DomainUser
    {
        $query = $this->repositoryQuery($options);
        $filters = $query->filters();
        $filters['email'] = $email->address;

        $model = $this->first(RepositoryQuery::fromOptions([
            'filters' => $filters,
            'include' => $query->includes(),
            'sort' => $query->sort(),
            'context' => $query->context(),
        ]));

        return $model ? UserMapper::toDomain($model) : null;
    }

    /**
     * Find by password reset token.
     */
    public function findUserByResetToken(AccessToken $token): ?DomainUser
    {
        $now = (new \DateTimeImmutable)->format('Y-m-d H:i:s');
        $model = $this->first(RepositoryQuery::fromOptions([
            'filters' => [
                'password_reset_token' => $token->get('token'),
                'password_reset_expires[gt]' => $now,
            ],
        ]));

        return $model ? UserMapper::toDomain($model) : null;
    }

    /**
     * Save a new domain user.
     */
    public function saveUser(DomainUser $user): void
    {
        $this->create(UserMapper::toEloquent($user));
    }

    /**
     * Update an existing domain user.
     */
    public function updateUser(DomainUser $user): void
    {
        $model = $this->show($user->getId()->get(), []);
        if ($model) {
            $this->update(UserMapper::toUpdatedEloquent($model, $user));
        }
    }

    /**
     * Delete user.
     */
    public function deleteUser(DomainUser $user): void
    {
        $model = $this->show($user->getId()->get(), []);
        if ($model) {
            $this->delete($model);
        }
    }

    // === Advanced helpers (clean usage of base API) ===

    public function findActiveUsersWithRecentLogin(): iterable
    {
        $filters = [
            'status' => 'active',
            'last_login_at[gte]' => (new \DateTimeImmutable('-30 days'))->format('Y-m-d H:i:s'),
        ];
        $opts = [
            'filters' => $filters,
            'sort' => '-last_login_at,username',
        ];
        $query = $this->query($opts);
        foreach ($this->all($query) as $user) {
            yield UserMapper::toDomain($user);
        }
    }

    public function findUsersWithCustomFilters(array $searchParams): iterable
    {
        $filters = [];
        if (! empty($searchParams['search'])) {
            $filters['search'] = new SearchFilter(
                $searchParams['search'],
                ['name', 'email', 'username']
            );
        }
        if (! empty($searchParams['created_from']) || ! empty($searchParams['created_to'])) {
            $filters['created_at'] = new DateRangeFilter(
                $searchParams['created_from'] ?? null,
                $searchParams['created_to'] ?? null
            );
        }
        $query = $this->query(['filters' => $filters]);
        foreach ($this->all($query) as $user) {
            yield UserMapper::toDomain($user);
        }
    }
}
