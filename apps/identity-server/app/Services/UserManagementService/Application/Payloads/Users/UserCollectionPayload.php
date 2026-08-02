<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Payloads\Users;

use App\Services\UserManagementService\Domain\Aggregates\User;
use Zolta\Cqrs\Contracts\MessagePayloadInterface;
use Zolta\Domain\ValueObjects\Pagination;

/**
 * @internal Wraps a collection of User aggregates for CQRS responses.
 *
 * @phpstan-type UserList array<int, User>
 */
final readonly class UserCollectionPayload implements MessagePayloadInterface
{
    /**
     * @param  Pagination<User>  $userPaginationCollection
     */
    public function __construct(private Pagination $userPaginationCollection) {}

    public function toArray(): array
    {
        return ['userPaginationCollection' => $this->userPaginationCollection];
    }
}
