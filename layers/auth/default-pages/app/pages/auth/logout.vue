<script setup lang="ts">
definePageMeta({ layout: 'identity-auth' })

const config = useRuntimeConfig()
const route = useRoute()
const mutate = useIdentityMutation()
const { logout } = useIdentityAuth()
const errorMessage = ref('')

onMounted(async () => {
  try {
    const application = typeof route.query.application === 'string' ? route.query.application : ''
    const intent = typeof route.query.intent === 'string' ? route.query.intent : ''
    if (application && intent) {
      const result = await mutate<{ redirectUrl: string }>('/api/hosted-auth/logout', {
        method: 'POST',
        body: { application, intent }
      })
      await navigateTo(result.redirectUrl, { external: true })
      return
    }
    await logout()
    await navigateTo(identitySafeRedirect(
      config.public.identityAuth.logoutRedirect,
      '/auth/login'
    ))
  } catch (error) {
    errorMessage.value = identityAuthErrorMessage(
      error,
      'We could not close your session.'
    )
  }
})
</script>

<template>
  <IdentityAuthCard
    title="Signing out"
    description="Closing your application session."
  >
    <p
      v-if="errorMessage"
      class="identity-auth-error"
    >
      {{ errorMessage }}
    </p>
  </IdentityAuthCard>
</template>
