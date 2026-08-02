<script setup lang="ts">
import type { IdentityAuthenticationContext } from '../../../../shared/types/identity-auth'

definePageMeta({ layout: 'identity-auth' })

const route = useRoute()
const config = useRuntimeConfig()
const auth = useIdentityAuth()
const form = reactive({ email: '', password: '' })
const pending = ref(false)
const errorMessage = ref('')
const demoPending = ref(false)
const demoErrorMessage = ref('')
const demoReady = ref(false)

const {
  data: experience,
  error: experienceError,
  pending: experiencePending
} = await useFetch('/api/auth/context')

const primary = computed(() => experience.value?.primary ?? null)
const liveEnabled = computed(() => primary.value?.project.mode === 'live')
const demoContext = computed<IdentityAuthenticationContext | null>(() => {
  if (primary.value?.project.mode === 'sandbox') return primary.value
  if (experience.value?.sandbox?.project.mode === 'sandbox') return experience.value.sandbox
  return null
})
const registrationEnabled = computed(() => (
  liveEnabled.value
  && primary.value?.project.registration_mode === 'public'
))
const demoName = computed(() => auth.user.value?.name ?? 'Temporary demo user')
const demoEmail = computed(() => auth.user.value?.email ?? 'Creating account…')
const demoExpiresAt = computed(() => {
  const expiresAt = auth.user.value?.expiresAt
    ?? auth.identity.value?.temporaryExpiresAt

  if (!expiresAt) return 'at the end of the sandbox session'

  return new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short'
  }).format(new Date(expiresAt))
})

function destination(): string {
  return identitySafeRedirect(
    route.query.redirect,
    config.public.identityAuth.loginRedirect
  )
}

async function submit() {
  pending.value = true
  errorMessage.value = ''

  try {
    await auth.login(form)
    await navigateTo(destination())
  } catch (error) {
    errorMessage.value = identityAuthErrorMessage(
      error,
      'We could not sign you in with those credentials.'
    )
  } finally {
    pending.value = false
  }
}

async function provisionDemo() {
  if (demoPending.value || demoReady.value || !demoContext.value) return

  if (auth.loggedIn.value && auth.identity.value?.isTemporary) {
    demoReady.value = true
    return
  }

  demoPending.value = true
  demoErrorMessage.value = ''

  try {
    await auth.createSandboxSession(demoContext.value.connection)
    demoReady.value = true
  } catch (error) {
    demoErrorMessage.value = identityAuthErrorMessage(
      error,
      'We could not prepare the temporary demo account.'
    )
  } finally {
    demoPending.value = false
  }
}

onMounted(() => {
  if (
    demoContext.value?.connection === 'primary'
    && demoContext.value.project.mode === 'sandbox'
  ) {
    void provisionDemo()
  }
})
</script>

<template>
  <IdentityAuthCard
    :title="liveEnabled ? 'Sign in' : 'Demo access'"
    :description="liveEnabled
      ? `Use your ${primary?.project.name ?? 'application'} account to continue.`
      : 'Identity is preparing a temporary, pre-verified account for this sandbox.'"
  >
    <p
      v-if="experienceError"
      class="identity-auth-error"
    >
      We could not load this application's authentication settings.
    </p>

    <div
      v-else-if="experiencePending"
      class="identity-auth-status"
    >
      Loading authentication…
    </div>

    <template v-else>
      <form
        v-if="liveEnabled"
        class="identity-auth-form"
        @submit.prevent="submit"
      >
        <p
          v-if="errorMessage"
          class="identity-auth-error"
        >
          {{ errorMessage }}
        </p>
        <label class="identity-auth-field">
          Email
          <input
            v-model="form.email"
            type="email"
            autocomplete="email"
            required
          >
        </label>
        <label class="identity-auth-field">
          Password
          <input
            v-model="form.password"
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
          {{ pending ? 'Signing in…' : 'Sign in' }}
        </button>
      </form>

      <section
        v-if="demoContext"
        class="identity-auth-demo"
      >
        <template v-if="demoReady">
          <p class="identity-auth-success">
            Your temporary demo account is ready.
          </p>
          <label class="identity-auth-field">
            Demo name
            <input
              :value="demoName"
              type="text"
              readonly
            >
          </label>
          <label class="identity-auth-field">
            Demo email
            <input
              :value="demoEmail"
              type="email"
              readonly
            >
          </label>
          <label class="identity-auth-field">
            Password
            <input
              value="Not required — secure temporary session"
              type="text"
              readonly
            >
          </label>
          <p class="identity-auth-expiry">
            This account and its application data expire {{ demoExpiresAt }}.
          </p>
          <button
            class="identity-auth-button"
            type="button"
            @click="navigateTo(destination())"
          >
            Continue as demo
          </button>
        </template>
        <template v-else>
          <p
            v-if="demoErrorMessage"
            class="identity-auth-error"
          >
            {{ demoErrorMessage }}
          </p>
          <button
            class="identity-auth-button identity-auth-button--secondary"
            type="button"
            :disabled="demoPending"
            @click="provisionDemo"
          >
            {{ demoPending ? 'Preparing demo…' : 'Create instant demo account' }}
          </button>
        </template>
      </section>

      <p
        v-if="liveEnabled"
        class="identity-auth-links"
      >
        <NuxtLink to="/auth/forgot-password">
          Forgot password?
        </NuxtLink>
        <NuxtLink
          v-if="registrationEnabled"
          to="/auth/register"
        >
          Create account
        </NuxtLink>
      </p>
    </template>
  </IdentityAuthCard>
</template>
