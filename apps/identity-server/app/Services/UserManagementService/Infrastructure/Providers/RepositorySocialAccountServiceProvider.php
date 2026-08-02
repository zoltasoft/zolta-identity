<?php

namespace App\Services\UserManagementService\Infrastructure\Providers;

use App\Services\UserManagementService\Domain\Repositories\OAuthAccountRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentOAuthAccountRepository;
use Illuminate\Support\ServiceProvider;

class RepositorySocialAccountServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OAuthAccountRepository::class, EloquentOAuthAccountRepository::class);
    }
}
