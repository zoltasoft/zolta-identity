<script setup lang="ts">
definePageMeta({
  layout: 'identity-auth',
  middleware: 'identity-live-auth'
})

const config = useRuntimeConfig()
const { register } = useIdentityAuth()
const form = reactive({
  username: '',
  email: '',
  password: '',
  passwordConfirmation: ''
})
const pending = ref(false)
const errorMessage = ref('')

async function submit() {
  pending.value = true
  errorMessage.value = ''

  try {
    await register(form)
    await navigateTo(identitySafeRedirect(
      config.public.identityAuth.registerRedirect,
      '/auth/verify-email'
    ))
  } catch (error) {
    errorMessage.value = identityAuthErrorMessage(
      error,
      'We could not create your account.'
    )
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <IdentityAuthCard
    title="Create account"
    description="Create an account for this application."
  >
    <form
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
        Name
        <input
          v-model="form.username"
          type="text"
          autocomplete="name"
          minlength="2"
          required
        >
      </label>
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
          autocomplete="new-password"
          minlength="12"
          required
        >
      </label>
      <label class="identity-auth-field">
        Confirm password
        <input
          v-model="form.passwordConfirmation"
          type="password"
          autocomplete="new-password"
          minlength="12"
          required
        >
      </label>
      <button
        class="identity-auth-button"
        type="submit"
        :disabled="pending"
      >
        {{ pending ? 'Creating account…' : 'Create account' }}
      </button>
    </form>
    <p class="identity-auth-links">
      <NuxtLink to="/auth/login">
        Already have an account?
      </NuxtLink>
    </p>
  </IdentityAuthCard>
</template>
