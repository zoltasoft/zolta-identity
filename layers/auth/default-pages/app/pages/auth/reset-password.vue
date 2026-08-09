<script setup lang="ts">
import { useIdentityMutation } from '../../../../app/composables/useIdentityMutation'

definePageMeta({
  layout: 'identity-auth',
  middleware: 'identity-live-auth'
})

const route = useRoute()
const { resetPassword } = useIdentityAuth()
const mutateIdentity = useIdentityMutation()
const hostedClientId = computed(() => typeof route.query.client_id === 'string' ? route.query.client_id : '')
const form = reactive({
  email: typeof route.query.email === 'string' ? route.query.email : '',
  token: typeof route.query.token === 'string' ? route.query.token : '',
  password: '',
  passwordConfirmation: ''
})
const pending = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const applicationUrl = ref('')

async function submit() {
  pending.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    if (hostedClientId.value) {
      const result = await mutateIdentity<{ applicationUrl: string }>('/api/hosted-auth/password/reset', {
        method: 'POST',
        body: { clientId: hostedClientId.value, ...form }
      })
      applicationUrl.value = result.applicationUrl
    } else {
      await resetPassword(form)
    }
    successMessage.value = 'Your password has been reset. You can now sign in.'
  } catch (error) {
    errorMessage.value = identityAuthErrorMessage(
      error,
      'We could not reset your password.'
    )
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <IdentityAuthCard
    title="Choose a new password"
    description="Enter the reset token and a new password."
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
      <p
        v-if="successMessage"
        class="identity-auth-success"
      >
        {{ successMessage }}
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
        Reset token
        <input
          v-model="form.token"
          type="text"
          autocomplete="one-time-code"
          minlength="64"
          required
        >
      </label>
      <label class="identity-auth-field">
        New password
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
        {{ pending ? 'Resetting…' : 'Reset password' }}
      </button>
    </form>
    <p class="identity-auth-links">
      <a
        v-if="applicationUrl"
        :href="applicationUrl"
      >
        Return to your application
      </a>
      <NuxtLink
        v-else
        to="/auth/login"
      >
        Continue to sign in
      </NuxtLink>
    </p>
  </IdentityAuthCard>
</template>
