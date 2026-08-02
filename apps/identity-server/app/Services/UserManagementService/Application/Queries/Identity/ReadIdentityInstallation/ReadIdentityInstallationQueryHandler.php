<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Identity\ReadIdentityInstallation;

use App\Services\UserManagementService\Application\Contracts\IdentityInstallationServiceInterface;
use App\Services\UserManagementService\Application\Payloads\Identity\IdentityOperationPayload;
use InvalidArgumentException;
use Zolta\Cqrs\Attributes\HandlesQuery;
use Zolta\Cqrs\Services\Option;

#[HandlesQuery(ReadIdentityInstallationQuery::class)]
final readonly class ReadIdentityInstallationQueryHandler
{
    public function __construct(private IdentityInstallationServiceInterface $identity) {}

    public function __invoke(ReadIdentityInstallationQuery $query): Option
    {
        if ($query->operation !== 'installation.users.index') {
            throw new InvalidArgumentException("Unsupported Identity installation query [{$query->operation}].");
        }

        return Option::some(new IdentityOperationPayload(
            $this->identity->listInstallationUsers($query->actorUserId),
        ));
    }
}
