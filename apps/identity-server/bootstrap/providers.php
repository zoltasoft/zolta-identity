<?php

use App\Providers\AppServiceProvider;
use App\Services\UserManagementService\Infrastructure\Providers\UserManagementServiceProvider;

return [
    AppServiceProvider::class,
    UserManagementServiceProvider::class,
];
