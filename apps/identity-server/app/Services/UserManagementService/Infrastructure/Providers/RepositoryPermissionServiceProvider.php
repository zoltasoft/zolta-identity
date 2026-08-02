<?php

namespace App\Services\UserManagementService\Infrastructure\Providers;

use App\Services\UserManagementService\Domain\Factories\PermissionFactory;
use App\Services\UserManagementService\Domain\Repositories\PermissionRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentPermissionRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryPermissionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PermissionRepository::class, EloquentPermissionRepository::class);
        $this->app->bind(PermissionFactory::class);
    }
}
