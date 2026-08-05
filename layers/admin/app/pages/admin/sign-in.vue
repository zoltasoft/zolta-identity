<script setup lang="ts">
definePageMeta({ layout: false })

const route = useRoute()
const localePath = useLocalePath()
const { t } = useI18n()
const { login } = useIdentityAuth()
const form = reactive({ email: '', password: '' })
const pending = ref(false)
const errorMessage = ref('')

function resolveSignInError(error: unknown): string {
  const candidate = error as {
    statusCode?: number
    statusMessage?: string
    data?: {
      message?: string
      statusMessage?: string
    }
  }

  const rawMessage = candidate.data?.message
    ?? candidate.data?.statusMessage
    ?? candidate.statusMessage
    ?? ''
  const normalized = rawMessage.toLowerCase()
  const statusCode = candidate.statusCode

  if (
    statusCode === 400
    || statusCode === 422
    || normalized.includes('validation')
    || normalized.includes('required')
    || normalized.includes('invalid email')
    || normalized.includes('password confirmation')
  ) {
    return 'Please review the email and password fields and try again.'
  }

  if (
    statusCode === 401
    || normalized.includes('invalid credentials')
    || normalized.includes('unauthorized')
    || normalized.includes('incorrect password')
    || normalized.includes('wrong password')
    || normalized.includes('email or password')
  ) {
    return 'The email or password you entered is incorrect. Please try again.'
  }

  if (
    statusCode === 429
    || normalized.includes('too many attempts')
    || normalized.includes('rate limit')
  ) {
    return 'Too many sign-in attempts. Please wait a moment and try again.'
  }

  if (
    statusCode === 503
    || statusCode === 500
    || normalized.includes('temporarily unavailable')
    || normalized.includes('service unavailable')
    || normalized.includes('server')
  ) {
    return 'The identity service is temporarily unavailable. Please try again in a moment.'
  }

  return t('identityConsole.signIn.error')
}

async function submit() {
  pending.value = true
  errorMessage.value = ''
  try {
    await login(form)
    const projectsPath = localePath('/admin/projects')
    const installationUsersPath = localePath('/admin/identity-users')
    const requestedRedirect = typeof route.query.redirect === 'string'
      ? route.query.redirect
      : ''
    const destination = [projectsPath, installationUsersPath].some(path =>
      requestedRedirect === path || requestedRedirect.startsWith(`${path}/`)
    )
      ? requestedRedirect
      : projectsPath
    await navigateTo(destination)
  } catch (error: unknown) {
    errorMessage.value = resolveSignInError(error)
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <main class="relative flex min-h-screen items-center justify-center bg-default px-6 py-12">
    <div class="absolute end-4 top-4 sm:end-6 sm:top-6">
      <IdentityConsoleControls />
    </div>

    <div class="w-full max-w-md">
      <div class="mb-4 flex items-center gap-3 rounded-2xl border border-default bg-elevated/40 px-3 py-2">
        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary text-inverted shadow-sm">
          <UIcon
            name="i-lucide-shield-check"
            class="size-5"
          />
        </span>

        <div class="min-w-0 space-y-0.5">
          <p class="truncate text-sm font-medium text-highlighted">
            {{ t('identityConsole.brand') }}
          </p>
        </div>
      </div>

      <UPageCard
        class="w-full"
      >
        <p class="-mt-1 text-sm leading-6 text-muted">
          {{ t('identityConsole.signIn.description') }}
        </p>

        <form
          class="space-y-5"
          @submit.prevent="submit"
        >
          <UFormField
            :label="t('identityConsole.signIn.email')"
            required
          >
            <UInput
              v-model="form.email"
              type="email"
              autocomplete="email"
              class="w-full"
            />
          </UFormField>
          <UFormField
            :label="t('identityConsole.signIn.password')"
            required
          >
            <UInput
              v-model="form.password"
              type="password"
              autocomplete="current-password"
              class="w-full"
            />
          </UFormField>
          <UAlert
            v-if="errorMessage"
            color="error"
            variant="soft"
            :description="errorMessage"
          />
          <UButton
            type="submit"
            block
            :label="t('identityConsole.signIn.submit')"
            icon="i-lucide-log-in"
            :loading="pending"
          />
        </form>
      </UPageCard>
    </div>
  </main>
</template>
