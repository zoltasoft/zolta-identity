<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Identity;

use App\Services\UserManagementService\Application\DTOs\Input\IdentityOperationDTO;
use App\Services\UserManagementService\Application\Enums\Identity\IdentitySessionReadOperation;
use App\Services\UserManagementService\Application\Queries\Identity\ReadIdentityAccess\ReadIdentityAccessQuery;
use App\Services\UserManagementService\Application\Queries\Identity\ReadIdentitySession\ReadIdentitySessionQuery;
use InvalidArgumentException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class ReadIdentityAuthenticationService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(IdentityOperationDTO $dto): mixed
    {
        if ($dto->operation === 'auth.context') {
            return $this->execute(ReadIdentityAccessQuery::class, [
                'clientId' => (string) $dto->input['client_id'],
                'clientSecret' => (string) $dto->input['client_secret'],
                'project' => isset($dto->input['project'])
                    ? (string) $dto->input['project']
                    : null,
            ]);
        }

        $operation = IdentitySessionReadOperation::tryFrom($dto->operation)
            ?? throw new InvalidArgumentException(
                "Unsupported Identity authentication query [{$dto->operation}].",
            );

        return $this->execute(ReadIdentitySessionQuery::class, [
            'operation' => $operation,
            'input' => $dto->input,
            'actorUserId' => $dto->actorUserId,
            'accessToken' => $dto->accessToken,
        ]);
    }

    /** @param class-string $query @param array<string, mixed> $arguments */
    private function execute(string $query, array $arguments): mixed
    {
        ['result' => $result] = $this->applicationService
            ->runAndCapture($query, $arguments)
            ->getOrFail();

        return $result;
    }
}
