<script setup lang="ts">
definePageMeta({
  layout: 'identity-auth',
  middleware: ['identity-live-auth', 'identity-auth']
})

const config = useRuntimeConfig()
const { verifyEmail, resendVerification, fetch } = useIdentityAuth()
const code = ref('')
const pending = ref(false)
const resending = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

async function submit() {
  pending.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await verifyEmail(code.value)
    await fetch()
    successMessage.value = 'Your email address is verified.'
    await navigateTo(identitySafeRedirect(
      config.public.identityAuth.loginRedirect,
      '/'
    ))
  } catch (error) {
    errorMessage.value = identityAuthErrorMessage(
      error,
      'We could not verify that code.'
    )
  } finally {
    pending.value = false
  }
}

async function resend() {
  resending.value = true
  errorMessage.value = ''

  try {
    await resendVerification()
    successMessage.value = 'A new verification code has been sent.'
  } catch (error) {
    errorMessage.value = identityAuthErrorMessage(
      error,
      'We could not send a new verification code.'
    )
  } finally {
    resending.value = false
  }
}
</script>

<template>
  <IdentityAuthCard
    title="Verify your email"
    description="Enter the six-digit code sent to your email address."
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
        Verification code
        <input
          v-model="code"
          type="text"
          inputmode="numeric"
          autocomplete="one-time-code"
          pattern="[0-9]{6}"
          maxlength="6"
          required
        >
      </label>
      <button
        class="identity-auth-button"
        type="submit"
        :disabled="pending"
      >
        {{ pending ? 'Verifying…' : 'Verify email' }}
      </button>
      <button
        class="identity-auth-button"
        type="button"
        :disabled="resending"
        @click="resend"
      >
        {{ resending ? 'Sending…' : 'Send a new code' }}
      </button>
    </form>
  </IdentityAuthCard>
</template>
