<?php

return [
    'access_token_ttl_minutes' => (int) env('IDENTITY_ACCESS_TOKEN_TTL_MINUTES', 15),
    'refresh_token_ttl_days' => (int) env('IDENTITY_REFRESH_TOKEN_TTL_DAYS', 30),
    'invitation_ttl_hours' => (int) env('IDENTITY_INVITATION_TTL_HOURS', 72),
    'email_verification_ttl_minutes' => (int) env('IDENTITY_EMAIL_VERIFICATION_TTL_MINUTES', 15),
    'password_reset_ttl_minutes' => (int) env('IDENTITY_PASSWORD_RESET_TTL_MINUTES', 60),
    'expose_development_tokens' => (bool) env('IDENTITY_EXPOSE_DEVELOPMENT_TOKENS', false),
    'password_reset_url' => env('IDENTITY_PASSWORD_RESET_URL'),
    'consumer' => [
        'base_url' => env('IDENTITY_API_URL'),
        'client_id' => env('IDENTITY_CLIENT_ID'),
        'client_secret' => env('IDENTITY_CLIENT_SECRET'),
        'local' => (bool) env('IDENTITY_INTROSPECTION_LOCAL', false),
        'introspection_cache_seconds' => (int) env('IDENTITY_INTROSPECTION_CACHE_SECONDS', 30),
    ],
];
