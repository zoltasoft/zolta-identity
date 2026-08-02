<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Authentication\ListAccountSessions;

use App\Services\UserManagementService\Application\Contracts\AccountSecurityServiceInterface;
use App\Services\UserManagementService\Application\Payloads\Authentication\AccountSessionCollectionPayload;
use Zolta\Cqrs\Attributes\HandlesQuery;
use Zolta\Cqrs\Services\Option;

#[HandlesQuery(ListAccountSessionsQuery::class)]
final readonly class ListAccountSessionsQueryHandler
{
    public function __construct(private AccountSecurityServiceInterface $accountSecurity) {}

    public function __invoke(ListAccountSessionsQuery $query): Option
    {
        return Option::some(new AccountSessionCollectionPayload(
            $this->accountSecurity->listSessions($query->userId, $query->currentTokenId)
        ));
    }
}
