# Identity API

This Laravel 13 application is the independently deployable API for [ZoltaSoft's Laravel Zolta Identity](https://github.com/zoltasoft/zolta-identity). Its `UserManagementService` slice provides authentication and account lifecycle, global user/role/permission administration, social accounts, project access management, confidential clients, rotating sessions, audit, and consumer introspection.

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan identity:bootstrap owner@example.com --name="Installation owner"
php artisan serve --host=127.0.0.1 --port=8100
```

The bootstrap secret is shown once and belongs only to the Nuxt Identity Console BFF.

Requirements are PHP 8.3+, Composer 2, and SQLite, MySQL, or PostgreSQL. For a repeatable local setup after cloning, `composer setup` installs dependencies, creates `.env` and the SQLite database when absent, generates the application key, and runs migrations. Run `identity:bootstrap` separately so its one-time credentials can be stored deliberately.

## API groups

- `/api/auth/*`: authentication and account-lifecycle endpoints
- `/api/users/*`, `/api/roles/*`, `/api/permissions/*`: installation-level administration protected by Laravel system-administrator enforcement
- `/api/v1/identity/auth/*`: register, login, refresh, introspect, current identity, sessions, and logout
- `/api/v1/identity/projects/*`: projects, clients, permission manifests, roles, permissions, invitations, memberships, and audit events
- `/api/v1/identity/users/*`: installation-level user administration
- `/api/v1/identity/invitations/accept`: invitation acceptance

Management endpoints require a project-scoped Sanctum access token. System-wide user management requires a system administrator. Project changes require either that state or an active project-administrator membership.

## Token rules

- access-token TTL: `IDENTITY_ACCESS_TOKEN_TTL_MINUTES`, default 15 minutes
- refresh-token TTL: `IDENTITY_REFRESH_TOKEN_TTL_DAYS`, default 30 days
- invitation TTL: `IDENTITY_INVITATION_TTL_HOURS`, default 72 hours
- refresh tokens rotate atomically
- reuse of a rotated token revokes the complete refresh family and its access tokens
- disabling a client, locking a user, removing a membership, or changing relevant access invalidates affected authorization

## Verification

```bash
composer check
composer security:audit
php artisan route:list --path=api
php artisan route:list --path=api/v1/identity
```

Give the identity server its own database and deployment lifecycle. Consumer applications should store only their domain data and the global identity IDs needed to associate it with users.
