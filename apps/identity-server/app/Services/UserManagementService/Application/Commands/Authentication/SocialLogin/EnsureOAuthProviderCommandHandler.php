<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\SocialLogin;

use App\Services\UserManagementService\Application\Payloads\Authentication\OAuthProviderPayload;
use App\Services\UserManagementService\Domain\Entities\OAuthProvider;
use App\Services\UserManagementService\Domain\Factories\OAuthProviderFactory as DomainOAuthProviderFactory;
use App\Services\UserManagementService\Domain\Repositories\OAuthProviderRepository;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;
use Zolta\Domain\ValueObjects\OAuthProvider as OAuthProviderEnum;

#[HandlesCommand(EnsureOAuthProviderCommand::class)]
final readonly class EnsureOAuthProviderCommandHandler
{
    public function __construct(
        private OAuthProviderRepository $oAuthProviderRepository,
        private DomainOAuthProviderFactory $domainOAuthProviderFactory
    ) {}

    public function __invoke(EnsureOAuthProviderCommand $ensureOAuthProviderCommand): Result
    {
        $oAuthProvider = OAuthProviderEnum::fromString($ensureOAuthProviderCommand->provider);

        $provider = $this->oAuthProviderRepository->findByOAuthProvider($oAuthProvider);
        if (! $provider instanceof OAuthProvider) {
            $provider = $this->domainOAuthProviderFactory->create($oAuthProvider);
            $this->oAuthProviderRepository->saveOAuthProvider($provider);
        }

        return Result::success(new OAuthProviderPayload($provider));
    }
}
