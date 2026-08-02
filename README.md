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
- live and sandbox projects with credentialless, time-limited identities
- project-managed HMAC cleanup webhooks with persisted delivery retries
- credential and social login, registration, verification, password recovery, account sessions, profile/security settings, account export/deletion, and temporary accounts
- global and project-scoped user, role, and permission administration

## Architecture and security model

Browser credentials and identity tokens are handled by the Nuxt BFF. Client secrets, access tokens, and refresh tokens are kept in its encrypted, HTTP-only session and are never returned to browser JavaScript. Access tokens expire quickly; refresh tokens rotate and reuse revokes the whole session family.

Create a different confidential client for every BFF, API, worker, environment, and trust boundary. Never reuse the Identity Console client in a consumer application.

Identity owns global account fields: email, password hash, username, avatar, verification state, lock state, and system-administrator state. Consumer applications own their domain records and preferences such as locale, theme, notification settings, jobs, and documents.

The Nuxt application follows a layered BFF architecture: browser code calls Nuxt server routes, and those routes call the identity API. The Laravel application keeps its HTTP, application, domain, and infrastructure concerns separated inside `UserManagementService` and uses Zolta CQRS and HTTP packages for dispatch and routing.

The Nuxt package includes project-aware default authentication pages. Live
projects receive the permanent-account login, registration, verification, and
recovery experience. Sandbox projects receive an automatically provisioned,
pre-verified temporary account with its generated identity and expiry displayed
before continuing. Extend `@zoltasoft/identity-nuxt/default-pages` to use these
pages or the headless `@zoltasoft/identity-nuxt` entry to keep custom pages.

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

For production, set `APP_ENV=production`, `APP_DEBUG=false`, secure HTTPS URLs, persistent cache and queue backends, and a production database. Keep every client secret in the hosting platform's secret manager rather than a committed file.

## Connect a consumer application

Create two clients for a typical Nuxt + Laravel application:

- `<Application> Nuxt BFF` logs users in and stores its token pair server-side.
- `<Application> Laravel API` introspects BFF access tokens and enforces permission keys.

The BFF calls `POST /api/v1/identity/auth/login` or `POST /api/v1/identity/auth/register` with its own client credentials. The API calls `POST /api/v1/identity/auth/introspect` with the API client's credentials and the presented bearer token.

A successful introspection includes the global user ID, project, token-issuing client, roles, permissions, authorization version, email-verification state, session family, and expiration time. Consumer APIs must verify the required permission for every protected route.

For a consumer Laravel API, configure remote introspection with its dedicated API client:

```dotenv
IDENTITY_API_URL=http://127.0.0.1:8100
IDENTITY_CLIENT_ID=<application-api-client-id>
IDENTITY_CLIENT_SECRET=<application-api-client-secret>
IDENTITY_PROJECT=<application-project>
```

The application's Nuxt BFF must use a separate confidential client. Do not put either client secret in public Nuxt runtime configuration.

### Reusable Nuxt authentication layer

The `@zoltasoft/identity-nuxt` workspace package is the supported Nuxt BFF
integration. It owns the encrypted identity session, CSRF-protected
`/api/auth/*` routes, token rotation, a route middleware, and
`useIdentityAuth()`. It has two entries:

- `@zoltasoft/identity-nuxt` is headless and lets an application keep fully
  custom login, signup, recovery, reset, and verification pages.
- `@zoltasoft/identity-nuxt/default-pages` adds a ready-to-use authentication
  interface at `/auth/login`, `/auth/register`,
  `/auth/forgot-password`, `/auth/reset-password`,
  `/auth/verify-email`, and `/auth/logout`.

Extend one entry from the consumer's `nuxt.config.ts`:

```ts
export default defineNuxtConfig({
  extends: ['@zoltasoft/identity-nuxt/default-pages']
})
```

For custom pages, extend `@zoltasoft/identity-nuxt` instead and call
`useIdentityAuth()`. Browser code always calls the consumer's local
`/api/auth/*` BFF routes; it never sends the confidential client secret or
bearer tokens to the browser.

Configure the consumer Nuxt server with its own BFF client:

```dotenv
NUXT_SESSION_PASSWORD=<at-least-32-random-characters>
IDENTITY_API_URL=http://127.0.0.1:8100
IDENTITY_PROJECT=<consumer-project>
IDENTITY_CLIENT_ID=<consumer-nuxt-bff-client-id>
IDENTITY_CLIENT_SECRET=<consumer-nuxt-bff-client-secret>
```

Cookie names and default-page product labels and redirects are optional. See
[`docs/nuxt-auth-consumer-layer.md`](docs/nuxt-auth-consumer-layer.md) and
[`layers/auth/README.md`](layers/auth/README.md) for the complete contract and
custom-page example.

## Project onboarding

New projects default to `invite_only`. A project administrator can change the policy in the console:

- `invite_only`: users join using a one-time invitation.
- `public`: the project's BFF may register a new global account and membership directly.

A public project may designate one project role as the default role for newly registered members. Registering an email that already belongs to a global account is rejected; the user signs in and joins through an invitation or a future account-linking flow instead.

## Account deletion boundary

Removing a user from an application should delete that application's records and remove its project membership. It must not delete the global identity account. Global account deletion is an identity-level action because it affects every connected project.

The identity API does not reach into consumer databases. Configure a signed cleanup webhook per project. Identity emits `identity.user.expired` for sandbox expiry and `identity.user.deletion_requested` for explicit global deletion. Explicit deletion remains pending until subscribed consumers acknowledge cleanup.

## API surface

The identity server exposes account-level, installation-level, and project-scoped endpoints:

- `/api/auth/*`: registration, credential/social login, logout, refresh, verification, password recovery/change, sessions, export, deletion, and temporary accounts
- `/api/users/*`: account profile/security preferences and system-administrator user management
- `/api/roles/*` and `/api/permissions/*`: installation-wide RBAC
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
layers/auth/                 Publishable Nuxt BFF auth package and optional default pages
packages/ui/                 Nuxt UI foundation
packages/i18n/               Internationalization layer
packages/laravel-consumer/   Publishable remote-introspection and webhook-verification package
apps/identity-server/        Standalone Laravel identity API
```

## Deployment checklist

1. Provision separate production storage for the identity API; never point it at a consumer application's database.
2. Configure HTTPS URLs, a strong Laravel application key, a strong Nuxt session password, database backups, mail delivery, and persistent cache/queue services.
3. Run `composer --working-dir=apps/identity-server install --no-dev --classmap-authoritative` and `pnpm install --frozen-lockfile`, then build the Nuxt application.
4. Run `php artisan migrate --force`, then bootstrap the installation owner and Identity Console client.
5. Store each printed client secret immediately, distribute it only to its intended service, and verify remote introspection before directing traffic.
6. Run queue workers under a process supervisor when `QUEUE_CONNECTION` is asynchronous, and monitor authentication failures, token reuse, client rotation, and audit events.
7. Verify the `/up` health endpoint, authentication flows, token refresh and introspection, queue processing, mail delivery, and database backups before directing production traffic.

## Contributing and security

Contributions are welcome; see [CONTRIBUTING.md](CONTRIBUTING.md) for the development workflow. Report vulnerabilities privately as described in [SECURITY.md](SECURITY.md), never through a public issue. Source, issues, and releases are hosted in the [ZoltaSoft repository](https://github.com/zoltasoft/zolta-identity).

## License

Released under the [MIT License](LICENSE).
