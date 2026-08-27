<?php

namespace App\Services\UserManagementService\Infrastructure\Providers;

use App\Services\UserManagementService\Application\Contracts\AccountDataEraserInterface;
use App\Services\UserManagementService\Application\Contracts\AccountDataExporterInterface;
use App\Services\UserManagementService\Application\Contracts\AccountSecurityServiceInterface;
use App\Services\UserManagementService\Application\Contracts\AuthenticationServiceInterface;
use App\Services\UserManagementService\Application\Contracts\OAuthGateway;
use App\Services\UserManagementService\Application\Contracts\PasswordRecoveryServiceInterface;
use App\Services\UserManagementService\Application\Contracts\RateLimitingServiceInterface;
use App\Services\UserManagementService\Application\Contracts\SecretGenerator;
use App\Services\UserManagementService\Application\Contracts\SendWelcomeMessageServiceInterface;
use App\Services\UserManagementService\Application\Contracts\TemporaryAccountManagerInterface;
use App\Services\UserManagementService\Application\Listeners\SendWelcomeEmailListener;
use App\Services\UserManagementService\Infrastructure\Services\EloquentTemporaryAccountManager;
use App\Services\UserManagementService\Infrastructure\Services\LaravelAccountDataEraser;
use App\Services\UserManagementService\Infrastructure\Services\LaravelAccountDataExporter;
use App\Services\UserManagementService\Infrastructure\Services\LaravelAccountSecurityService;
use App\Services\UserManagementService\Infrastructure\Services\LaravelPasswordRecoveryService;
use App\Services\UserManagementService\Infrastructure\Services\LaravelRateLimitingService;
use App\Services\UserManagementService\Infrastructure\Services\LaravelSecretGenerator;
use App\Services\UserManagementService\Infrastructure\Services\SanctumAuthenticationService;
use App\Services\UserManagementService\Infrastructure\Services\SocialiteOAuthGateway;
use Illuminate\Support\ServiceProvider;

class AuthenticationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthenticationServiceInterface::class, SanctumAuthenticationService::class);
        $this->app->bind(OAuthGateway::class, SocialiteOAuthGateway::class);
        $this->app->bind(SecretGenerator::class, LaravelSecretGenerator::class);
        $this->app->bind(AccountDataEraserInterface::class, LaravelAccountDataEraser::class);
        $this->app->bind(AccountDataExporterInterface::class, LaravelAccountDataExporter::class);
        $this->app->bind(AccountSecurityServiceInterface::class, LaravelAccountSecurityService::class);
        $this->app->bind(PasswordRecoveryServiceInterface::class, LaravelPasswordRecoveryService::class);
        $this->app->bind(RateLimitingServiceInterface::class, LaravelRateLimitingService::class);
        $this->app->bind(SendWelcomeMessageServiceInterface::class, SendWelcomeEmailListener::class);
        $this->app->bind(TemporaryAccountManagerInterface::class, EloquentTemporaryAccountManager::class);
    }
}
