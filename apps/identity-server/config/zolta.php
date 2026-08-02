<?php

declare(strict_types=1);

return [
    'commands' => [
        [
            'path' => app_path('Services/UserManagementService'),
            'namespace' => 'App\\Services\\UserManagementService\\',
        ],
    ],
    'queries' => [
        [
            'path' => app_path('Services/UserManagementService'),
            'namespace' => 'App\\Services\\UserManagementService\\',
        ],
    ],
    'infrastructure_events' => [
        [
            'path' => app_path('Services/UserManagementService'),
            'namespace' => 'App\\Services\\UserManagementService\\',
        ],
    ],
    'cache' => [
        'command' => base_path('bootstrap/cache/command_map.php'),
        'query' => base_path('bootstrap/cache/query_map.php'),
        'event' => base_path('bootstrap/cache/event_map.php'),
    ],
    'map_keys' => [
        'command' => 'command.map',
        'query' => 'query.map',
        'event' => 'event.map',
    ],
    'options' => [
        'auto_detect_psr4' => false,
        'write_atomic' => true,
        'file_pattern' => '*.php',
        'exclude_paths' => [
            '**/Persistence/Seeders/**',
            '**/Persistence/Factories/**',
            '**/Persistence/Migrations/**',
            '**/Infrastructure/Persistence/Migrations/**',
            '**/Infrastructure/Repositories/**',
            '**/API/Routes/**',
            '**/Database/**',
            '**/vendor/**',
        ],
        'composer_autoload' => base_path('vendor/autoload.php'),
        'follow_symlinks' => false,
        'verbose_logging' => false,
    ],
];
