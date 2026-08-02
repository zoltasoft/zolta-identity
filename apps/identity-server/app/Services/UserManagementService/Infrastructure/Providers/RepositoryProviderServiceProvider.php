<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Providers;

use App\Services\UserManagementService\Domain\Repositories\IdentityClientRepository;
use App\Services\UserManagementService\Domain\Repositories\IdentityMembershipRepository;
use App\Services\UserManagementService\Domain\Repositories\IdentityPermissionRepository;
use App\Services\UserManagementService\Domain\Repositories\IdentityProjectRepository;
use App\Services\UserManagementService\Domain\Repositories\IdentityRoleRepository;
use App\Services\UserManagementService\Domain\Repositories\IdentityWebhookRepository;
use App\Services\UserManagementService\Domain\Repositories\OAuthProviderRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityClientRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityMembershipRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityPermissionRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityProjectRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityRoleRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentIdentityWebhookRepository;
use App\Services\UserManagementService\Infrastructure\Repositories\EloquentOAuthProviderRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryProviderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IdentityClientRepository::class, EloquentIdentityClientRepository::class);
        $this->app->bind(IdentityMembershipRepository::class, EloquentIdentityMembershipRepository::class);
        $this->app->bind(IdentityPermissionRepository::class, EloquentIdentityPermissionRepository::class);
        $this->app->bind(IdentityProjectRepository::class, EloquentIdentityProjectRepository::class);
        $this->app->bind(IdentityRoleRepository::class, EloquentIdentityRoleRepository::class);
        $this->app->bind(IdentityWebhookRepository::class, EloquentIdentityWebhookRepository::class);
        $this->app->bind(OAuthProviderRepository::class, EloquentOAuthProviderRepository::class);
    }
}
