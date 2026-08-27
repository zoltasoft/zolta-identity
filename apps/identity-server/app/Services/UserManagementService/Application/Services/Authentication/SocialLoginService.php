<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Authentication;

use App\Services\UserManagementService\Application\Commands\Authentication\SocialLogin\EnsureOAuthProviderCommand;
use App\Services\UserManagementService\Application\Commands\Authentication\SocialLogin\FetchSocialUserCommand;
use App\Services\UserManagementService\Application\Commands\Authentication\SocialLogin\ResolveSocialUserCommand;
use App\Services\UserManagementService\Application\Commands\Authentication\SocialLogin\SyncOAuthAccountCommand;
use App\Services\UserManagementService\Application\DTOs\External\AuthenticatedUser;
use App\Services\UserManagementService\Application\DTOs\External\OAuthUser;
use App\Services\UserManagementService\Application\DTOs\Input\SocialLoginDTO;
use App\Services\UserManagementService\Application\DTOs\Output\OAuthResponseDTO;
use App\Services\UserManagementService\Application\Queries\Authentication\GenerateTokenFromUser\GenerateTokenFromUserQuery;
use App\Services\UserManagementService\Domain\Aggregates\User;
use App\Services\UserManagementService\Domain\Entities\OAuthProvider;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Domain\ValueObjects\AccessToken as AccessTokenVO;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class SocialLoginService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(SocialLoginDTO $socialLoginDTO): OAuthResponseDTO
    {
        $this->applicationService->capture(['input' => ['provider' => $socialLoginDTO->provider]]);

        $socialPayload = $this->applicationService->runAndCapture(FetchSocialUserCommand::class, [
            'provider' => $socialLoginDTO->provider,
            'accessToken' => $socialLoginDTO->accessToken,
        ])->getOrFail(static fn (): RuntimeException => new RuntimeException('Unable to communicate with the social provider.'));

        $oauthUser = $this->extractPayloadValue(
            $socialPayload,
            'oauthUser',
            OAuthUser::class,
            'Unable to communicate with the social provider.'
        );

        $providerPayload = $this->applicationService->runAndCapture(EnsureOAuthProviderCommand::class, [
            'provider' => $socialLoginDTO->provider,
        ])->getOrFail();

        $oAuthProvider = $this->extractPayloadValue(
            $providerPayload,
            'provider',
            OAuthProvider::class,
            'Unable to resolve social provider.'
        );

        $userPayload = $this->applicationService->runAndCapture(ResolveSocialUserCommand::class, [
            'email' => $oauthUser->email,
            'name' => $oauthUser->name,
        ])->getOrFail(static fn (): RuntimeException => new RuntimeException('Unable to resolve user account.'));

        $user = $this->extractPayloadValue(
            $userPayload,
            'user',
            User::class,
            'Unable to resolve user account.'
        );

        $this->applicationService->runAndCapture(SyncOAuthAccountCommand::class, [
            'userId' => $user->getId()->get('value'),
            'providerId' => $oAuthProvider->getId()->get('value'),
            'providerUserId' => $oauthUser->providerUserId,
            'email' => $oauthUser->email,
            'accessToken' => $oauthUser->accessToken,
            'refreshToken' => $oauthUser->refreshToken,
            'avatarUrl' => $oauthUser->avatarUrl,
        ])->getOrFail();

        $tokenResult = $this->applicationService->runAndCapture(GenerateTokenFromUserQuery::class, [
            'id' => $user->getId(),
        ])->getOrFail(static fn (): RuntimeException => new RuntimeException('Unable to issue an access token.'));

        $accessToken = $this->extractPayloadValue(
            $tokenResult,
            'accessToken',
            AccessTokenVO::class,
            'Unable to issue an access token.'
        );

        return new OAuthResponseDTO(
            accessToken: $accessToken->get('token'),
            user: AuthenticatedUser::fromDomain($user)
        );
    }

    /**
     * @template T of object
     *
     * @param  array<string, mixed>  $payload
     * @param  class-string<T>  $expectedClass
     * @return T
     */
    private function extractPayloadValue(array $payload, string $key, string $expectedClass, string $message): object
    {
        $value = $payload[$key] ?? null;

        if (! $value instanceof $expectedClass) {
            throw new RuntimeException($message);
        }

        return $value;
    }
}
