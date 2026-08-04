<script setup lang="ts">
definePageMeta({ layout: false })

const route = useRoute()
const localePath = useLocalePath()
const { t } = useI18n()
const { login } = useIdentityAuth()
const form = reactive({ email: '', password: '' })
const pending = ref(false)
const errorMessage = ref('')

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
    errorMessage.value = (error as { data?: { message?: string }, statusMessage?: string }).data?.message
      ?? (error as { statusMessage?: string }).statusMessage
      ?? t('identityConsole.signIn.error')
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
    <UPageCard
      class="w-full max-w-md"
      :title="t('identityConsole.brand')"
      :description="t('identityConsole.signIn.description')"
    >
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
  </main>
</template>
