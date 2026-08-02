<?php

declare(strict_types=1);

use Zolta\Http\Response\Laravel\Resources\GenericResource;

return [
    'routes' => [
        'cache' => [
            'enabled' => env('ZOLTA_ATTR_ROUTE_CACHE', true),
            'ensure_fresh_on_boot' => env('ZOLTA_ATTR_ROUTE_ENSURE_FRESH_ON_BOOT', true),
            'skip_commands' => [
                'package:discover',
                'make:zolta-update-namespace',
            ],
        ],
        'paths' => [app_path('Services/UserManagementService/API/Controllers')],
        'documentation' => ['enabled' => false],
        'default_response' => GenericResource::class,
    ],
];
