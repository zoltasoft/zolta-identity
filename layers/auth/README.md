# @zoltasoft/identity-nuxt

Reusable server-side Nuxt authentication for Laravel Zolta Identity.

## Choose an entry

```ts
// Ready-made /auth/* pages
export default defineNuxtConfig({
  extends: ['@zoltasoft/identity-nuxt/default-pages']
})
```

```ts
// Headless integration for custom application pages
export default defineNuxtConfig({
  extends: ['@zoltasoft/identity-nuxt']
})
```

The headless entry provides:

- encrypted HTTP-only sessions through `nuxt-auth-utils`
- CSRF protection for browser mutations
- local login, registration, logout, recovery, reset, verification, and
  session-management BFF routes under `/api/auth/*`
- rotating-token refresh with concurrent-refresh deduplication
- `useIdentityAuth()` for custom pages
- `identity-auth` route middleware for protected pages
- `@zoltasoft/identity-nuxt/types` for browser-safe identity types

## Server configuration

```dotenv
NUXT_SESSION_PASSWORD=<at-least-32-random-characters>
IDENTITY_API_URL=http://127.0.0.1:8100
IDENTITY_PROJECT=<project-slug-or-id>
IDENTITY_CLIENT_ID=<nuxt-bff-client-id>
IDENTITY_CLIENT_SECRET=<nuxt-bff-client-secret>
```

Credentialless demos use a separate project configured as `sandbox` in the Identity Console:

```dotenv
IDENTITY_SANDBOX_API_URL=http://127.0.0.1:8100
IDENTITY_SANDBOX_PROJECT=<sandbox-project-slug-or-id>
IDENTITY_SANDBOX_CLIENT_ID=<sandbox-nuxt-bff-client-id>
IDENTITY_SANDBOX_CLIENT_SECRET=<sandbox-nuxt-bff-client-secret>
NUXT_PUBLIC_IDENTITY_SANDBOX_ENABLED=true
```

`useIdentityAuth().createSandboxSession()` calls the local `/api/auth/sandbox-session` route. Identity creates a verified temporary user without a password or verification email. The encrypted BFF session remembers the selected connection so refresh and logout use the sandbox client.

The ready-made pages resolve their experience from Identity at runtime:

- a live primary project renders sign-in, public registration, email
  verification, password recovery, and password reset
- an optional sandbox connection adds a **Create instant demo account** action
- a sandbox primary project hides permanent-account forms, immediately creates a
  temporary identity, and shows its generated name, email, and expiry before the
  user continues

Client secrets and issued tokens never enter the rendered page. Applications can
still extend the headless entry and supply custom pages while keeping the same
Identity APIs and encrypted BFF session behavior.

The API URL may be the server origin or include `/api/v1/identity`. Tokens and
client credentials remain private runtime configuration.

Optional settings:

```dotenv
IDENTITY_SESSION_COOKIE_NAME=my-app-session
IDENTITY_CSRF_COOKIE_NAME=my-app-csrf
NUXT_PUBLIC_IDENTITY_AUTH_PRODUCT_NAME="My application"
NUXT_PUBLIC_IDENTITY_AUTH_LOGIN_REDIRECT=/dashboard
NUXT_PUBLIC_IDENTITY_AUTH_LOGOUT_REDIRECT=/auth/login
NUXT_PUBLIC_IDENTITY_AUTH_REGISTER_REDIRECT=/auth/verify-email
```

Only local redirect paths are accepted by the default pages.

## Custom page example

```vue
<script setup lang="ts">
const { login } = useIdentityAuth()
const form = reactive({ email: '', password: '' })

async function submit() {
  await login(form)
  await navigateTo('/dashboard')
}
</script>

<template>
  <form @submit.prevent="submit">
    <input v-model="form.email" type="email">
    <input v-model="form.password" type="password">
    <button type="submit">Sign in</button>
  </form>
</template>
```

Consumers may keep application-specific user fields and permission mapping in a
thin server adapter. The package preserves existing user and secure-session
fields when it rotates identity tokens.

This BFF package does not create cross-application SSO. Authorization code plus
PKCE/OIDC remains the future protocol for shared sign-on between independently
hosted applications.
