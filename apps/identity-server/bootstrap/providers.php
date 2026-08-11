<?php

use App\Providers\AppServiceProvider;
use App\Providers\TelescopeServiceProvider;
use App\Services\UserManagementService\Infrastructure\Providers\UserManagementServiceProvider;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

return [
    AppServiceProvider::class,
    UserManagementServiceProvider::class,
    ...class_exists(TelescopeApplicationServiceProvider::class)
        ? [TelescopeServiceProvider::class]
        : [],
];
