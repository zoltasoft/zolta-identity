<script setup lang="ts">
definePageMeta({ layout: 'identity-auth' })

const config = useRuntimeConfig()
const { logout } = useIdentityAuth()
const errorMessage = ref('')

onMounted(async () => {
  try {
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
