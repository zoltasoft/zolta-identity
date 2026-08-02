<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Authentication;

use App\Services\UserManagementService\Application\DTOs\External\AuthenticatedUser;
use App\Services\UserManagementService\Application\DTOs\Input\RefreshDTO;
use App\Services\UserManagementService\Application\DTOs\Output\RefreshTokenResponseDTO;
use App\Services\UserManagementService\Application\Queries\Authentication\GenerateTokenFromUser\GenerateTokenFromUserQuery;
use App\Services\UserManagementService\Application\Queries\Users\GetUserById\GetUserByIdQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class RefreshTokenService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(RefreshDTO $refreshDTO): RefreshTokenResponseDTO
    {
        $this->applicationService->capture(['input' => [
            'userId' => $refreshDTO->userId,
        ]]);

        $userResult = $this->applicationService->runAndCapture(GetUserByIdQuery::class, [
            'id' => $refreshDTO->userId,
        ])->getOrFail(static fn (): RuntimeException => new RuntimeException('Authenticated user not found.'));

        $user = $userResult['user'];

        $tokenResult = $this->applicationService->runAndCapture(GenerateTokenFromUserQuery::class, [
            'id' => $refreshDTO->userId,
        ])->getOrFail(static fn (): RuntimeException => new RuntimeException('Unable to issue an access token.'));

        $accessToken = $tokenResult['accessToken'];

        return new RefreshTokenResponseDTO(
            accessToken: $accessToken->get('token'),
            user: AuthenticatedUser::fromDomain($user)
        );
    }
}
