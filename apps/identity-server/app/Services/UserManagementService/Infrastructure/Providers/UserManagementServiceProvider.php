<?php

namespace App\Services\UserManagementService\Infrastructure\Providers;

use App\Services\UserManagementService\Infrastructure\Persistence\Providers\MigrationServiceProvider;
use Illuminate\Support\ServiceProvider;

class UserManagementServiceProvider extends ServiceProvider
{
    /**
     * Register all library service providers in order.
     */
    public function register(): void
    {
        $this->app->register(AuthenticationServiceProvider::class);
        $this->app->register(IdentityAccessServiceProvider::class);
        $this->app->register(ListenerServiceProvider::class);
        $this->app->register(MigrationServiceProvider::class);
        $this->app->register(RepositoryProviderServiceProvider::class);
        $this->app->register(RepositorySocialAccountServiceProvider::class);
        $this->app->register(RepositoryUserServiceProvider::class);
        $this->app->register(MailerServiceProvider::class);
    }

    /**
     * Boot method if needed.
     */
    public function boot(): void {}
}
