<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Users;

use App\Services\UserManagementService\Application\DTOs\Input\GetUserByEmailDTO;
use App\Services\UserManagementService\Application\DTOs\Output\GetUserByEmailResponseDTO;
use App\Services\UserManagementService\Application\Queries\Users\GetUserByEmail\GetUserByEmailQuery;
use App\Services\UserManagementService\Domain\Aggregates\User;
use App\Services\UserManagementService\Domain\Exceptions\UserNotFoundException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Exceptions\Rest\NotFoundException;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class GetUserByEmailService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(GetUserByEmailDTO $getUserByEmailDTO): GetUserByEmailResponseDTO
    {
        $this->applicationService->capture(['input' => $getUserByEmailDTO->toArray()]);

        $userResult = $this->applicationService
            ->cqrs()
            ->run(GetUserByEmailQuery::class, [
                'email' => $getUserByEmailDTO->address,
            ])
            ->getOrFail(fn () => throw new UserNotFoundException);

        $user = $userResult['user'] ?? null;

        if (! $user instanceof User) {
            throw new NotFoundException('Unable to resolve user aggregate from query payload.');
        }

        return GetUserByEmailResponseDTO::fromDomain($user);
    }
}
