# Laravel Zolta Identity

[![CI](https://github.com/zoltasoft/zolta-identity/actions/workflows/ci.yml/badge.svg)](https://github.com/zoltasoft/zolta-identity/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

Laravel Zolta Identity is an open-source, standalone identity provider and administration console maintained by [ZoltaSoft](https://github.com/zoltasoft). It gives multiple applications a shared account system while keeping each application's business data and preferences independent.

## Features

The repository contains:

- a Nuxt 4 Identity Console and confidential BFF on `http://127.0.0.1:3100`
- a Laravel 13 identity API on `http://127.0.0.1:8100`
- project-scoped users, memberships, roles, permissions, and confidential clients
- short-lived Sanctum access tokens and rotating refresh-token families
- confidential-client token introspection for APIs
- invitation-only or public onboarding per project
- the complete migrated User Management service: credential and social login, registration, verification, password recovery, account sessions, profile/security settings, account export/deletion, temporary accounts, global users, roles, and permissions
- an idempotent legacy-data importer

## Architecture and security model

Browser credentials and identity tokens are handled by the Nuxt BFF. Client secrets, access tokens, and refresh tokens are kept in its encrypted, HTTP-only session and are never returned to browser JavaScript. Access tokens expire quickly; refresh tokens rotate and reuse revokes the whole session family.

Create a different confidential client for every BFF, API, worker, environment, and trust boundary. Never reuse the Identity Console client in a consumer application.

Identity owns global account fields: email, password hash, username, avatar, verification state, lock state, and system-administrator state. Consumer applications own their domain records and preferences such as locale, theme, notification settings, jobs, and documents.

The Nuxt application follows a layered BFF architecture: browser code calls Nuxt server routes, and those routes call the identity API. The Laravel application keeps its HTTP, application, domain, and infrastructure concerns separated inside `UserManagementService` and uses Zolta CQRS and HTTP packages for dispatch and routing.

## Requirements

- Node.js `^22.12`, `^24.11`, or `>=26` (matching Nuxt's supported release lines)
- pnpm 10.30+
- PHP 8.3 or newer
- Composer 2
- SQLite for local development, or MySQL/PostgreSQL in hosted environments

## Quick start

Clone the repository, then set up the Laravel API:

```bash
git clone git@github.com:zoltasoft/zolta-identity.git
cd zolta-identity
```

```bash
cd apps/identity-server
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan identity:bootstrap owner@example.com --name="Installation owner"
php artisan serve --host=127.0.0.1 --port=8100
```

The bootstrap command prompts for a password of at least 12 characters and prints the Identity Console client ID and secret once. Store both immediately.

In another terminal, set up the Nuxt console:

```bash
pnpm install
cp .env.example .env
```

Generate a session password and put the bootstrap client credentials in the root `.env`:

```dotenv
NUXT_SESSION_PASSWORD=<at-least-32-random-characters>
IDENTITY_API_URL=http://127.0.0.1:8100
IDENTITY_PROJECT=identity-console
IDENTITY_CLIENT_ID=<identity-console-bff-client-id>
IDENTITY_CLIENT_SECRET=<identity-console-bff-client-secret>
```

Then run:

```bash
pnpm dev
```

Open `http://127.0.0.1:3100/admin/sign-in` and use the bootstrap owner's email and password.

## Configuration

Copy both committed environment examples and keep the resulting `.env` files local. The main settings are:

| Variable | Application | Purpose |
| --- | --- | --- |
| `NUXT_SESSION_PASSWORD` | Nuxt | Encrypts the server-side browser session; use at least 32 random characters. |
| `IDENTITY_API_URL` | Nuxt | Base URL of the standalone Laravel API. |
| `IDENTITY_PROJECT` | Nuxt | Project key used by the Identity Console. |
| `IDENTITY_CLIENT_ID` / `IDENTITY_CLIENT_SECRET` | Nuxt | Confidential BFF credentials; never expose them through public runtime config. |
| `NUXT_VITE_ALLOWED_HOSTS` | Nuxt | Optional comma-separated development hosts accepted by Vite. |
| `IDENTITY_CONSOLE_URL` | Laravel | Console origin used in identity flows. |
| `IDENTITY_*_TTL_*` | Laravel | Access-token, refresh-token, invitation, verification, and password-reset lifetimes. |
| `LEGACY_IDENTITY_DB_*` | Laravel | Read-only source connection used only by the legacy importer. |

For production, set `APP_ENV=production`, `APP_DEBUG=false`, secure HTTPS URLs, persistent cache and queue backends, and a production database. Keep every client secret in the hosting platform's secret manager rather than a committed file.

## Migrate the embedded Portfolio identity data

Keep the current Portfolio database unchanged and point the standalone API at a separate, empty database. Configure the read-only legacy connection in `apps/identity-server/.env`:

```dotenv
LEGACY_IDENTITY_DB_CONNECTION=sqlite
LEGACY_IDENTITY_DB_DATABASE=/absolute/path/to/portfolio/apps/interviewlike-server/database/database.sqlite
```

For MySQL, also set the legacy host, port, username, and password variables shown in the Laravel `.env.example`.

Run the standalone migrations and importer before bootstrapping a new owner in the destination:

```bash
cd apps/identity-server
php artisan migrate
php artisan identity:migrate-legacy
```

The importer:

- preserves user UUIDs, email/password hashes, verification/security state, global roles and permissions, social providers/accounts, projects, memberships, project roles/permissions, invitations, audit records, and client IDs
- never imports legacy access or refresh sessions
- rotates the secret of each newly imported confidential client and prints each new secret once
- leaves already-imported users and rotated client secrets unchanged on later runs
- configures `interviewlike-job-tracker` for public registration with its `member` role when that role exists

Update each BFF/API environment with the newly printed client secret before cutover. If a secret was not stored, rotate it from the Identity Console.

## Connect a consumer application

Create two clients for a typical Nuxt + Laravel application:

- `<Application> Nuxt BFF` logs users in and stores its token pair server-side.
- `<Application> Laravel API` introspects BFF access tokens and enforces permission keys.

The BFF calls `POST /api/v1/identity/auth/login` or `POST /api/v1/identity/auth/register` with its own client credentials. The API calls `POST /api/v1/identity/auth/introspect` with the API client's credentials and the presented bearer token.

A successful introspection includes the global user ID, project, token-issuing client, roles, permissions, authorization version, email-verification state, session family, and expiration time. Consumer APIs must verify the required permission for every protected route.

For the Portfolio Laravel API, use remote introspection:

```dotenv
IDENTITY_API_URL=http://127.0.0.1:8100
IDENTITY_CLIENT_ID=<portfolio-api-client-id>
IDENTITY_CLIENT_SECRET=<portfolio-api-client-secret>
IDENTITY_INTROSPECTION_LOCAL=false
```

The Portfolio Nuxt BFF uses its separate BFF client variables. Do not put either client secret in public Nuxt runtime configuration.

## Project onboarding

New projects default to `invite_only`. A project administrator can change the policy in the console:

- `invite_only`: users join using a one-time invitation.
- `public`: the project's BFF may register a new global account and membership directly.

A public project may designate one project role as the default role for newly registered members. Registering an email that already belongs to a global account is rejected; the user signs in and joins through an invitation or a future account-linking flow instead.

## Account deletion boundary

Removing a user from an application should delete that application's records and remove its project membership. It must not delete the global identity account. Global account deletion is an identity-level action because it affects every connected project.

The identity API intentionally does not reach into consumer databases. Before globally deleting an account, connected applications must erase or transfer their own data through their application-specific offboarding workflow.

## Compatibility API

The original Zolta `UserManagementService` is available alongside the project-scoped API so existing applications can migrate without losing features:

- `/api/auth/*`: registration, credential/social login, logout, refresh, verification, password recovery/change, sessions, export, deletion, and temporary accounts
- `/api/users/*`: account profile/security preferences and system-administrator user management
- `/api/roles/*` and `/api/permissions/*`: installation-wide compatibility RBAC
- `/api/v1/identity/*`: confidential clients, project memberships, project RBAC, rotating sessions, introspection, and audit

Installation-wide user, role, and permission routes enforce `is_system_admin` inside Laravel. Project administrators use project-scoped endpoints and cannot elevate themselves to installation administrators.

## Commands and checks

Run the complete local validation suite from the repository root:

```bash
pnpm check
pnpm build
pnpm security:audit
```

Individual commands are also available:

```bash
# Nuxt console
pnpm dev
pnpm lint
pnpm test:frontend

# Laravel
pnpm dev:api
pnpm lint:php
pnpm test:backend

# Direct Laravel inspection
cd apps/identity-server
composer check
php artisan route:list --path=api/v1/identity
```

GitHub Actions runs Composer validation, Laravel formatting/tests, Nuxt linting/type checks, and the production Nuxt build for pushes to `main` and pull requests. Dependabot checks the pnpm, Composer, and GitHub Actions ecosystems weekly.

## Repository layout

```text
app/                         Nuxt shell and shared BFF composables
layers/admin/                Identity Console pages and identity BFF routes
layers/auth/                 Encrypted session and CSRF configuration
packages/ui/                 Nuxt UI foundation
packages/i18n/               Internationalization layer
apps/identity-server/        Standalone Laravel identity API
```

## Deployment checklist

1. Provision separate production storage for the identity API; never point it at a consumer application's database.
2. Configure HTTPS URLs, a strong Laravel application key, a strong Nuxt session password, database backups, mail delivery, and persistent cache/queue services.
3. Run `composer --working-dir=apps/identity-server install --no-dev --classmap-authoritative` and `pnpm install --frozen-lockfile`, then build the Nuxt application.
4. Run `php artisan migrate --force`, followed by the one-time bootstrap or legacy migration workflow as appropriate.
5. Store each printed client secret immediately, distribute it only to its intended service, and verify remote introspection before directing traffic.
6. Run queue workers under a process supervisor when `QUEUE_CONNECTION` is asynchronous, and monitor authentication failures, token reuse, client rotation, and audit events.
7. Keep the embedded identity service available for rollback during a migration observation window.

## Contributing and security

Contributions are welcome; see [CONTRIBUTING.md](CONTRIBUTING.md) for the development workflow. Report vulnerabilities privately as described in [SECURITY.md](SECURITY.md), never through a public issue. Source, issues, and releases are hosted in the [ZoltaSoft repository](https://github.com/zoltasoft/zolta-identity).

## License

Released under the [MIT License](LICENSE).
