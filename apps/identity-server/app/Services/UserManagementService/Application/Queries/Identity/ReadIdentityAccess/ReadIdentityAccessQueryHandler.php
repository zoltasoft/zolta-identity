<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Identity\ReadIdentityAccess;

use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\ReadIdentityAccessContext;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use Zolta\Cqrs\Attributes\HandlesQuery;
use Zolta\Cqrs\Services\Option;

#[HandlesQuery(ReadIdentityAccessQuery::class)]
final readonly class ReadIdentityAccessQueryHandler
{
    public function __construct(private ReadIdentityAccessContext $access) {}

    public function __invoke(ReadIdentityAccessQuery $query): Option
    {
        return Option::some(new IdentityOperationPayload(
            $this->access->authenticationContext(
                $query->clientId,
                $query->clientSecret,
                $query->project,
            ),
        ));
    }
}
