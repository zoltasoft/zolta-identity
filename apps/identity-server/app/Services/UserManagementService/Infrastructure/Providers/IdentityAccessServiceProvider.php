<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Providers;

use App\Services\UserManagementService\API\Middleware\IdentityIntrospectionMiddleware;
use App\Services\UserManagementService\API\Middleware\RequireIdentityAccessToken;
use App\Services\UserManagementService\Application\Contracts\IdentityAccessServiceInterface;
use App\Services\UserManagementService\Infrastructure\Services\EloquentIdentityAccessService;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class IdentityAccessServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IdentityAccessServiceInterface::class, EloquentIdentityAccessService::class);
        $this->mergeConfigFrom(config_path('identity.php'), 'identity');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/identity.php');
        $this->app->make(Router::class)->aliasMiddleware('identity.introspect', IdentityIntrospectionMiddleware::class);
        $this->app->make(Router::class)->aliasMiddleware('identity.token', RequireIdentityAccessToken::class);
    }
}
