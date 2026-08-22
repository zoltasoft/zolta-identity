<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\SocialLogin;

use App\Services\UserManagementService\Application\Contracts\OAuthGateway;
use App\Services\UserManagementService\Application\Payloads\Authentication\OAuthRemoteUserPayload;
use InvalidArgumentException;
use RuntimeException;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;

#[HandlesCommand(FetchSocialUserCommand::class)]
final readonly class FetchSocialUserCommandHandler
{
    public function __construct(private OAuthGateway $oauthGateway) {}

    public function __invoke(FetchSocialUserCommand $fetchSocialUserCommand): Result
    {
        try {
            $oauthUser = $this->oauthGateway->fetchUser(
                $fetchSocialUserCommand->provider,
                $fetchSocialUserCommand->accessToken,
            );
        } catch (InvalidArgumentException $exception) {
            throw new RuntimeException('Unsupported social provider selected.', 0, $exception);
        }

        if ($oauthUser->email === '') {
            throw new RuntimeException('The social provider did not return an email address.');
        }

        return Result::success(new OAuthRemoteUserPayload($oauthUser));
    }
}
