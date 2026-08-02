# Identity API

This Laravel 13 application is the independently deployable API for Laravel Zolta Identity. Its only application slice is the complete migrated `UserManagementService`: authentication and account lifecycle, global user/role/permission administration, social accounts, project access management, confidential clients, rotating sessions, audit, and consumer introspection.

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

- `/api/auth/*`: the original authentication and account-lifecycle contract
- `/api/users/*`, `/api/roles/*`, `/api/permissions/*`: the original administration contract, protected by Laravel system-administrator enforcement
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

## Legacy import

Configure a read-only `LEGACY_IDENTITY_DB_*` connection, migrate the destination database, then run:

```bash
php artisan identity:migrate-legacy
```

Run the importer against an empty destination before `identity:bootstrap`. The command copies global accounts/RBAC/social identities plus project access data. It never copies `personal_access_tokens` or `identity_refresh_tokens`, so every browser and service starts a new standalone session. It rotates secrets only for clients imported for the first time. Use `--no-rotate-clients` only for a controlled migration in which retaining old client secrets is explicitly intended.

## Verification

```bash
composer check
composer security:audit
php artisan route:list --path=api
php artisan route:list --path=api/v1/identity
```

Do not run this API against the Portfolio application's database after cutover. Each service must have its own database and deployment lifecycle.
