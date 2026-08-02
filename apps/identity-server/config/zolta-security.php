<?php

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;

return [
    /*
    |--------------------------------------------------------------------------
    | Authorization Matrix
    |--------------------------------------------------------------------------
    |
    | Define high-level abilities mapped to concrete permission strings and
    | configure how the current user exposes permissions. The Authorization
    | matrix is consumed by Zolta's authorization feature.
    |
    */

    'abilities' => [
        // Example abilities mapped to permission strings
        'admin.access' => [
            'users.manage',
        ],
    ],

    'user' => [
        // Optional: enforce a specific user class for permission extraction
        'class' => User::class,

        // Attribute paths used to extract permissions from the user.
        // Use dot-notation with wildcards to walk into nested objects/collections.
        // e.g. 'role.permissions.*.name' reads the 'name' property on each
        // Permission model in the user's role.permissions collection.
        'attributes' => [
            'permissions.*.name',
            'role.permissions.*.name',
            'roles.*.permissions.*.name',
        ],
    ],
];
