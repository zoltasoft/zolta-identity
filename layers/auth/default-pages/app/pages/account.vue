<script setup lang="ts">
import type { IdentityAccountSession } from '../../../shared/types/identity-auth'
import { useIdentityMutation } from '../../../app/composables/useIdentityMutation'

definePageMeta({ layout: 'identity-auth' })

type AccountContext = {
  application: { key: string, name: string, returnUrl: string }
  project: { mode: 'live' | 'sandbox' }
  authenticated: boolean
  user: { email: string, username: string } | null
}

type AccountProfile = {
  id: string
  email: string
  username?: string
  name?: string
  avatar_url?: string | null
}

const route = useRoute()
const mutate = useIdentityMutation()
const application = computed(() => typeof route.query.application === 'string' ? route.query.application : '')
const requestedTab = typeof route.query.tab === 'string' ? route.query.tab : ''
const tab = ref<'profile' | 'password' | 'sessions' | 'privacy'>(
  ['profile', 'password', 'sessions', 'privacy'].includes(requestedTab)
    ? requestedTab as 'profile' | 'password' | 'sessions' | 'privacy'
    : 'profile'
)
const tabs = [
  { value: 'profile', label: 'Profile' },
  { value: 'password', label: 'Password' },
  { value: 'sessions', label: 'Sessions' },
  { value: 'privacy', label: 'Privacy' }
] as const
const pending = ref(false)
const loadingAccount = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const login = reactive({ email: '', password: '' })
const profile = reactive({ username: '', email: '', avatarUrl: '' })
const password = reactive({ current: '', next: '', confirmation: '' })
const sessions = ref<IdentityAccountSession[]>([])
const deleteConfirmation = ref('')

const { data: context, error: contextError, refresh: refreshContext } = await useFetch<AccountContext>(
  '/api/hosted-account/context',
  { query: { application } }
)

function message(error: unknown, fallback: string) {
  const candidate = error as {
    data?: { message?: string, statusMessage?: string }
    statusMessage?: string
  }
  return candidate.data?.statusMessage
    ?? candidate.data?.message
    ?? candidate.statusMessage
    ?? fallback
}

async function loadAccount() {
  if (!context.value?.authenticated || !application.value) return
  loadingAccount.value = true
  errorMessage.value = ''
  try {
    const [account, accountSessions] = await Promise.all([
      $fetch<AccountProfile>('/api/hosted-account/profile', {
        query: { application: application.value }
      }),
      $fetch<IdentityAccountSession[]>('/api/hosted-account/sessions', {
        query: { application: application.value }
      })
    ])
    profile.username = account.username ?? account.name ?? ''
    profile.email = account.email
    profile.avatarUrl = account.avatar_url ?? ''
    sessions.value = accountSessions
  } catch (error) {
    errorMessage.value = message(error, 'We could not load your Identity account.')
  } finally {
    loadingAccount.value = false
  }
}

if (context.value?.authenticated) await loadAccount()

async function signIn() {
  if (pending.value) return
  pending.value = true
  errorMessage.value = ''
  try {
    await mutate('/api/hosted-account/login', {
      method: 'POST',
      body: { application: application.value, ...login }
    })
    login.password = ''
    await refreshContext()
    await loadAccount()
  } catch (error) {
    errorMessage.value = message(error, 'We could not sign you in to manage this account.')
  } finally {
    pending.value = false
  }
}

async function saveProfile() {
  pending.value = true
  errorMessage.value = ''
  successMessage.value = ''
  try {
    await mutate('/api/hosted-account/profile', {
      method: 'PATCH',
      body: {
        application: application.value,
        username: profile.username,
        email: profile.email,
        avatar_url: profile.avatarUrl || null
      }
    })
    successMessage.value = 'Your global Identity profile was updated.'
    await loadAccount()
  } catch (error) {
    errorMessage.value = message(error, 'We could not update your Identity profile.')
  } finally {
    pending.value = false
  }
}

async function changePassword() {
  if (password.next !== password.confirmation) {
    errorMessage.value = 'The password confirmation does not match.'
    return
  }
  pending.value = true
  errorMessage.value = ''
  successMessage.value = ''
  try {
    await mutate('/api/hosted-account/password', {
      method: 'PATCH',
      body: {
        application: application.value,
        current_password: password.current,
        password: password.next,
        password_confirmation: password.confirmation
      }
    })
    Object.assign(password, { current: '', next: '', confirmation: '' })
    successMessage.value = 'Your password was changed. Other sessions were revoked.'
    await loadAccount()
  } catch (error) {
    errorMessage.value = message(error, 'We could not change your password.')
  } finally {
    pending.value = false
  }
}

async function revokeSession(id: string) {
  pending.value = true
  errorMessage.value = ''
  try {
    await mutate(`/api/hosted-account/sessions/${id}?application=${encodeURIComponent(application.value)}`, {
      method: 'DELETE'
    })
    await loadAccount()
  } catch (error) {
    errorMessage.value = message(error, 'We could not revoke that session.')
  } finally {
    pending.value = false
  }
}

async function exportAccount() {
  pending.value = true
  errorMessage.value = ''
  try {
    const data = await $fetch<Record<string, unknown>>('/api/hosted-account/export', {
      query: { application: application.value }
    })
    const url = URL.createObjectURL(new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' }))
    const link = document.createElement('a')
    link.href = url
    link.download = `identity-account-${new Date().toISOString().slice(0, 10)}.json`
    link.click()
    URL.revokeObjectURL(url)
  } catch (error) {
    errorMessage.value = message(error, 'We could not export your account data.')
  } finally {
    pending.value = false
  }
}

async function deleteAccount() {
  if (deleteConfirmation.value !== 'DELETE') return
  pending.value = true
  errorMessage.value = ''
  try {
    await mutate('/api/hosted-account', {
      method: 'DELETE',
      body: { application: application.value, confirmation: 'DELETE' }
    })
    await navigateTo(context.value?.application.returnUrl ?? '/', { external: true })
  } catch (error) {
    errorMessage.value = message(error, 'We could not delete your account.')
  } finally {
    pending.value = false
  }
}

async function closePortal() {
  await mutate('/api/hosted-account/logout', { method: 'POST' }).catch(() => undefined)
  await navigateTo(context.value?.application.returnUrl ?? '/', { external: true })
}
</script>

<template>
  <section class="identity-account-card">
    <header class="identity-account-header">
      <div>
        <p class="identity-auth-eyebrow">
          Identity account
        </p>
        <h1>{{ context?.application.name || 'Account management' }}</h1>
        <p>Manage the global profile and security shared by your connected applications.</p>
      </div>
      <button
        v-if="context?.application.returnUrl"
        class="identity-account-secondary"
        type="button"
        @click="closePortal"
      >
        Return to application
      </button>
    </header>

    <p
      v-if="contextError"
      class="identity-auth-error"
    >
      We could not load this application's account settings.
    </p>
    <p
      v-else-if="errorMessage"
      class="identity-auth-error"
    >
      {{ errorMessage }}
    </p>
    <p
      v-if="successMessage"
      class="identity-auth-success"
    >
      {{ successMessage }}
    </p>

    <div
      v-if="context && !context.authenticated"
      class="identity-account-login"
    >
      <template v-if="context.project.mode === 'live'">
        <h2>Confirm your identity</h2>
        <p>Your credentials stay inside Identity and are never sent to {{ context.application.name }}.</p>
        <form
          class="identity-auth-form"
          @submit.prevent="signIn"
        >
          <label class="identity-auth-field">
            Email
            <input
              v-model="login.email"
              type="email"
              autocomplete="email"
              required
            >
          </label>
          <label class="identity-auth-field">
            Password
            <input
              v-model="login.password"
              type="password"
              autocomplete="current-password"
              minlength="8"
              required
            >
          </label>
          <button
            class="identity-auth-button"
            type="submit"
            :disabled="pending"
          >
            {{ pending ? 'Signing in…' : 'Continue securely' }}
          </button>
        </form>
      </template>
      <p
        v-else
        class="identity-auth-status"
      >
        Temporary sandbox accounts do not have persistent global account settings.
      </p>
    </div>

    <template v-else-if="context?.authenticated">
      <nav
        class="identity-account-tabs"
        aria-label="Account settings"
      >
        <button
          v-for="item in tabs"
          :key="item.value"
          type="button"
          :class="{ active: tab === item.value }"
          @click="tab = item.value"
        >
          {{ item.label }}
        </button>
      </nav>

      <p
        v-if="loadingAccount"
        class="identity-auth-status"
      >
        Loading account…
      </p>

      <form
        v-else-if="tab === 'profile'"
        class="identity-account-panel identity-auth-form"
        @submit.prevent="saveProfile"
      >
        <h2>Global profile</h2>
        <label class="identity-auth-field">Username<input
          v-model="profile.username"
          required
          minlength="3"
        ></label>
        <label class="identity-auth-field">Email<input
          v-model="profile.email"
          type="email"
          required
        ></label>
        <label class="identity-auth-field">Avatar URL<input
          v-model="profile.avatarUrl"
          type="url"
        ></label>
        <button
          class="identity-auth-button"
          type="submit"
          :disabled="pending"
        >
          Save profile
        </button>
      </form>

      <form
        v-else-if="tab === 'password'"
        class="identity-account-panel identity-auth-form"
        @submit.prevent="changePassword"
      >
        <h2>Change password</h2>
        <label class="identity-auth-field">Current password<input
          v-model="password.current"
          type="password"
          autocomplete="current-password"
          minlength="8"
          required
        ></label>
        <label class="identity-auth-field">New password<input
          v-model="password.next"
          type="password"
          autocomplete="new-password"
          minlength="8"
          required
        ></label>
        <label class="identity-auth-field">Confirm new password<input
          v-model="password.confirmation"
          type="password"
          autocomplete="new-password"
          minlength="8"
          required
        ></label>
        <button
          class="identity-auth-button"
          type="submit"
          :disabled="pending"
        >
          Change password
        </button>
      </form>

      <section
        v-else-if="tab === 'sessions'"
        class="identity-account-panel"
      >
        <h2>Active sessions</h2>
        <div
          v-if="sessions.length"
          class="identity-account-sessions"
        >
          <article
            v-for="accountSession in sessions"
            :key="accountSession.id"
          >
            <div>
              <strong>{{ accountSession.project?.name || 'Identity' }}</strong>
              <span>{{ accountSession.client?.name || 'Browser session' }}</span>
            </div>
            <span
              v-if="accountSession.current"
              class="identity-account-current"
            >Current</span>
            <button
              v-else
              type="button"
              :disabled="pending"
              @click="revokeSession(accountSession.id)"
            >
              Revoke
            </button>
          </article>
        </div>
        <p
          v-else
          class="identity-auth-status"
        >
          No active sessions were found.
        </p>
      </section>

      <section
        v-else
        class="identity-account-panel identity-account-privacy"
      >
        <div>
          <h2>Export account data</h2>
          <p>Download the data owned by your global Identity account.</p>
          <button
            class="identity-account-secondary"
            type="button"
            :disabled="pending"
            @click="exportAccount"
          >
            Export JSON
          </button>
        </div>
        <div class="identity-account-danger">
          <h2>Delete account</h2>
          <p>This permanently removes your global identity and signs you out of connected applications.</p>
          <label class="identity-auth-field">Type DELETE to confirm<input
            v-model="deleteConfirmation"
            autocomplete="off"
          ></label>
          <button
            type="button"
            :disabled="pending || deleteConfirmation !== 'DELETE'"
            @click="deleteAccount"
          >
            Delete account
          </button>
        </div>
      </section>
    </template>
  </section>
</template>

<style scoped>
.identity-account-card { box-sizing: border-box; width: min(100%, 58rem); border: 1px solid var(--identity-auth-border); border-radius: 1rem; background: var(--identity-auth-card); box-shadow: 0 1rem 3rem rgba(23, 32, 51, .08); padding: 2rem; }
.identity-account-header { display: flex; align-items: start; justify-content: space-between; gap: 2rem; }
.identity-account-header h1, .identity-account-panel h2, .identity-account-login h2 { margin: 0; }
.identity-account-header p:not(.identity-auth-eyebrow), .identity-account-panel p, .identity-account-login p { color: var(--identity-auth-muted); }
.identity-account-secondary, .identity-account-sessions button { min-height: 2.5rem; border: 1px solid var(--identity-auth-border); border-radius: .65rem; background: var(--identity-auth-card); color: inherit; cursor: pointer; padding: .55rem .8rem; font-weight: 650; }
.identity-account-login { width: min(100%, 28rem); margin: 2rem auto 0; }
.identity-account-tabs { display: flex; gap: .4rem; overflow-x: auto; margin: 1.75rem 0 1.25rem; border-bottom: 1px solid var(--identity-auth-border); }
.identity-account-tabs button { border: 0; border-bottom: 2px solid transparent; background: transparent; color: var(--identity-auth-muted); cursor: pointer; padding: .75rem 1rem; font: inherit; font-weight: 650; }
.identity-account-tabs button.active { border-color: #3157d5; color: #3157d5; }
.identity-account-panel { display: grid; gap: 1rem; }
.identity-account-panel.identity-auth-form { width: min(100%, 32rem); }
.identity-account-sessions { display: grid; gap: .75rem; }
.identity-account-sessions article { display: flex; align-items: center; gap: 1rem; border: 1px solid var(--identity-auth-border); border-radius: .75rem; padding: .9rem; }
.identity-account-sessions article div { display: grid; flex: 1; }
.identity-account-sessions article span { color: var(--identity-auth-muted); font-size: .85rem; }
.identity-account-current { border-radius: 999px; background: var(--identity-auth-status); padding: .3rem .6rem; }
.identity-account-privacy { grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr)); }
.identity-account-privacy > div { border: 1px solid var(--identity-auth-border); border-radius: .75rem; padding: 1rem; }
.identity-account-danger { border-color: #e05252 !important; }
.identity-account-danger button { min-height: 2.5rem; border: 0; border-radius: .65rem; background: #b42318; color: #fff; cursor: pointer; padding: .55rem .8rem; font-weight: 700; }
button:disabled { cursor: not-allowed; opacity: .6; }
@media (max-width: 640px) { .identity-account-card { padding: 1.25rem; } .identity-account-header { flex-direction: column; } }
</style>
