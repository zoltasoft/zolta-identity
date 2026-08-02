<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts;

use App\Services\UserManagementService\Domain\Aggregates\User;
use Zolta\Cqrs\Repositories\Query\QueryOptions;
use Zolta\Domain\ValueObjects\AccessToken;
use Zolta\Domain\ValueObjects\UserCredential;
use Zolta\Domain\ValueObjects\UserId;

interface AuthenticationServiceInterface
{
    public function generateTokenFromUser(UserId $userId): AccessToken;

    public function revokeUserToken(string $tokenId): void;

    public function revokeAllUserTokens(UserId $userId): void;

    public function generateAuthUserToken(): AccessToken;

    public function attemptLogin(UserCredential $userCredential): bool;

    public function getAuthenticatedUser(QueryOptions $queryOptions): ?User;

    public function logout(): void;
}
