<?php

namespace App\Services\UserManagementService\Infrastructure\Providers;

use App\Services\UserManagementService\Domain\Repositories\OAuthProviderRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentOAuthProviderRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryProviderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OAuthProviderRepository::class, EloquentOAuthProviderRepository::class);
    }
}
