<?php

namespace App\Services\UserManagementService\Infrastructure\Providers;

use App\Services\UserManagementService\Domain\Factories\RoleFactory;
use App\Services\UserManagementService\Domain\Repositories\RoleRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentRoleRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryRoleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RoleRepository::class, EloquentRoleRepository::class);
        $this->app->bind(RoleFactory::class);
    }
}
