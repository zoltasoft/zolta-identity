<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Authentication;

use App\Services\UserManagementService\Application\Commands\Authentication\RevokeAllUserAuthenticationTokens\RevokeAllUserAuthenticationTokensCommand;
use App\Services\UserManagementService\Application\Commands\Authentication\RevokeUserToken\RevokeUserTokenCommand;
use App\Services\UserManagementService\Application\DTOs\Output\LogoutResponseDTO;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class LogoutService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(array $data): LogoutResponseDTO
    {
        if (isset($data['token_id'])) {
            $this->applicationService->runAndCapture(RevokeUserTokenCommand::class, [
                'tokenId' => $data['token_id'],
            ])->getOrFail(static fn (): RuntimeException => new RuntimeException('Unable to revoke the provided access token.'));
        } else {
            $this->applicationService->runAndCapture(RevokeAllUserAuthenticationTokensCommand::class)->getOrFail(static fn (): RuntimeException => new RuntimeException('Unable to revoke access tokens.'));
        }

        return new LogoutResponseDTO('Successfully logged out.');
    }
}
