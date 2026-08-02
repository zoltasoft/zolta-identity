<?php

namespace App\Services\UserManagementService\Infrastructure\Providers;

use App\Services\UserManagementService\Domain\Factories\UserFactory;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryUserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(UserFactory::class);
    }
}
