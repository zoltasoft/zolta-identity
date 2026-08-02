<?php

namespace App\Services\UserManagementService\Infrastructure\Persistence\Providers;

use Illuminate\Support\ServiceProvider;

class MigrationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom([
            base_path('app/Services/UserManagementService/Infrastructure/Persistence/Migrations'),
        ]);
    }
}
