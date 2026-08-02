<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\SocialLogin;

use App\Services\UserManagementService\Application\Payloads\Authentication\OAuthAccountPayload;
use App\Services\UserManagementService\Domain\Factories\OAuthAccountFactory;
use App\Services\UserManagementService\Domain\Repositories\OAuthAccountRepository;
use DateTimeImmutable;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;
use Zolta\Domain\ValueObjects\AccessToken;
use Zolta\Domain\ValueObjects\AvatarUrl;
use Zolta\Domain\ValueObjects\OAuthProviderId;
use Zolta\Domain\ValueObjects\RefreshToken;
use Zolta\Domain\ValueObjects\UserId;

#[HandlesCommand(SyncOAuthAccountCommand::class)]
final readonly class SyncOAuthAccountCommandHandler
{
    public function __construct(
        private OAuthAccountRepository $oAuthAccountRepository,
        private OAuthAccountFactory $oAuthAccountFactory
    ) {}

    public function __invoke(SyncOAuthAccountCommand $syncOAuthAccountCommand): Result
    {
        $existing = $this->oAuthAccountRepository->findByProviderAndProviderUserId(
            new OAuthProviderId($syncOAuthAccountCommand->providerId),
            $syncOAuthAccountCommand->providerUserId
        );

        $accessToken = new AccessToken(
            $syncOAuthAccountCommand->accessToken,
            new DateTimeImmutable('+2 hours')
        );

        $refreshToken = $syncOAuthAccountCommand->refreshToken
            ? new RefreshToken($syncOAuthAccountCommand->refreshToken, new DateTimeImmutable('+30 days'))
            : RefreshToken::generate();

        $avatarUrl = $syncOAuthAccountCommand->avatarUrl
            ? new AvatarUrl($syncOAuthAccountCommand->avatarUrl)
            : new AvatarUrl(sprintf('https://www.gravatar.com/avatar/%s', md5(strtolower(trim($syncOAuthAccountCommand->email)))));

        $oAuthAccount = $this->oAuthAccountFactory->sync(
            $existing,
            new UserId($syncOAuthAccountCommand->userId),
            new OAuthProviderId($syncOAuthAccountCommand->providerId),
            $syncOAuthAccountCommand->providerUserId,
            $accessToken,
            $refreshToken,
            $avatarUrl
        );

        $this->oAuthAccountRepository->saveOAuthAccount($oAuthAccount);

        return Result::success(new OAuthAccountPayload($oAuthAccount));
    }
}
