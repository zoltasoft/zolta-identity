<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Authentication\GenerateTokenFromUser;

use App\Services\UserManagementService\Application\Contracts\AuthenticationServiceInterface;
use Zolta\Cqrs\Attributes\HandlesQuery;
use Zolta\Cqrs\Services\Option;

#[HandlesQuery(GenerateTokenFromUserQuery::class)]
final readonly class GenerateTokenFromUserQueryHandler
{
    public function __construct(
        private AuthenticationServiceInterface $authenticationService
    ) {}

    public function __invoke(GenerateTokenFromUserQuery $generateTokenFromUserQuery): Option
    {
        $accessToken = $this->authenticationService->generateTokenFromUser($generateTokenFromUserQuery->id);

        return Option::of(['accessToken' => $accessToken]);
    }
}
