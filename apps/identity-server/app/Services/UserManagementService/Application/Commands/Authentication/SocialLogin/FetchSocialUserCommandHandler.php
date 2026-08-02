<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\SocialLogin;

use App\Services\UserManagementService\Application\Payloads\Authentication\OAuthRemoteUserPayload;
use App\Services\UserManagementService\Infrastructure\DTOs\OAuthUser as InfrastructureOAuthUser;
use App\Services\UserManagementService\Infrastructure\Services\OAuthProviderFactory as SocialiteProviderFactory;
use RuntimeException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(FetchSocialUserCommand::class)]
final readonly class FetchSocialUserCommandHandler
{
    public function __construct(private SocialiteProviderFactory $socialiteProviderFactory) {}

    public function __invoke(FetchSocialUserCommand $fetchSocialUserCommand): Result
    {
        try {
            $adapter = $this->socialiteProviderFactory->make($fetchSocialUserCommand->provider);
        } catch (\InvalidArgumentException $exception) {
            throw new RuntimeException('Unsupported social provider selected.', 0, $exception);
        }

        $oAuthUser = $adapter->fetchOAuthUser($fetchSocialUserCommand->accessToken);

        if ($oAuthUser->email === '') {
            throw new RuntimeException('The social provider did not return an email address.');
        }

        return Result::success(new OAuthRemoteUserPayload(
            new InfrastructureOAuthUser(
                $oAuthUser->providerUserId,
                $oAuthUser->name,
                $oAuthUser->email,
                $oAuthUser->accessToken,
                $oAuthUser->refreshToken,
                $oAuthUser->avatarUrl,
            )
        ));
    }
}
