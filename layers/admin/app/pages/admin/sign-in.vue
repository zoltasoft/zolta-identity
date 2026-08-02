<script setup lang="ts">
definePageMeta({ layout: false })

const route = useRoute()
const localePath = useLocalePath()
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
      ?? 'Unable to sign in.'
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <main class="flex min-h-screen items-center justify-center bg-default px-6 py-12">
    <UPageCard
      class="w-full max-w-md"
      title="Identity Console"
      description="Sign in through the confidential server-side client. Credentials and tokens remain inside the BFF session."
    >
      <form
        class="space-y-5"
        @submit.prevent="submit"
      >
        <UFormField
          label="Email"
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
          label="Password"
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
          label="Sign in"
          icon="i-lucide-log-in"
          :loading="pending"
        />
      </form>
    </UPageCard>
  </main>
</template>
