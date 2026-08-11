<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Identity;

use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ResolveIdentityHostedApplications;
use App\Services\UserManagementService\Application\DTOs\Input\ResolveIdentityHostedApplicationDTO;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class ResolveIdentityHostedApplicationService
{
    public function __construct(private ResolveIdentityHostedApplications $applications) {}

    /** @return array<string, mixed> */
    public function __invoke(ResolveIdentityHostedApplicationDTO $dto): array
    {
        return $dto->byClient
            ? $this->applications->resolveHostedApplicationByClient($dto->application)
            : $this->applications->resolveHostedApplication($dto->application);
    }
}
