<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Identity\ReadIdentitySession;

use App\Services\UserManagementService\Application\Contracts\Identity\Authentication\ReadIdentitySessions;
use App\Services\UserManagementService\Application\Enums\Identity\IdentitySessionReadOperation;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use InvalidArgumentException;
use Zolta\Cqrs\Attributes\HandlesQuery;
use Zolta\Cqrs\Services\Option;

#[HandlesQuery(ReadIdentitySessionQuery::class)]
final readonly class ReadIdentitySessionQueryHandler
{
    public function __construct(private ReadIdentitySessions $sessions) {}

    public function __invoke(ReadIdentitySessionQuery $query): Option
    {
        $result = match ($query->operation) {
            IdentitySessionReadOperation::Introspect => $this->sessions->introspect(
                (string) $query->input['client_id'],
                (string) $query->input['client_secret'],
                (string) $query->input['token'],
            ),
            IdentitySessionReadOperation::Current => $this->sessions->currentIdentity(
                $this->actor($query),
                $this->token($query),
            ),
            IdentitySessionReadOperation::Index => $this->sessions->listSessions(
                $this->actor($query),
                $this->token($query),
            ),
        };

        return Option::some(new IdentityOperationPayload($result));
    }

    private function actor(ReadIdentitySessionQuery $query): string
    {
        return $query->actorUserId
            ?? throw new InvalidArgumentException('An authenticated Identity actor is required.');
    }

    private function token(ReadIdentitySessionQuery $query): string
    {
        return $query->accessToken
            ?? throw new InvalidArgumentException('An Identity access token is required.');
    }
}
