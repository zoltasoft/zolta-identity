<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Repositories;

use App\Services\UserManagementService\Domain\Aggregates\User;
use Zolta\Domain\Repositories\Query\AbstractQueryOptions;
use Zolta\Domain\ValueObjects\AccessToken;
use Zolta\Domain\ValueObjects\Email;
use Zolta\Domain\ValueObjects\Pagination;
use Zolta\Domain\ValueObjects\UserId;

interface UserRepository
{
    /** Domain: return list/pagination of domain users. Accepts domain-level query options. */
    public function findAllUsers(AbstractQueryOptions $queryOptions): Pagination;

    /** Get all (non-paginated) domain users — accept options (streaming etc.) */
    public function getAllUsers(AbstractQueryOptions $queryOptions): iterable;

    /** Find single resource with optional options (relations, filters, context) */
    public function findUserById(UserId $userId, ?AbstractQueryOptions $queryOptions = null): ?User;

    public function findUserByEmail(Email $email, ?AbstractQueryOptions $queryOptions = null): ?User;

    public function findUserByResetToken(AccessToken $accessToken): ?User;

    public function saveUser(User $user): void;

    public function updateUser(User $user): void;

    public function deleteUser(User $user): void;
}
