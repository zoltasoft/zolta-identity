<script setup lang="ts">
import { useIdentityMutation } from '../../../../app/composables/useIdentityMutation'

definePageMeta({
  layout: 'identity-auth',
  middleware: 'identity-live-auth'
})

const route = useRoute()
const { forgotPassword } = useIdentityAuth()
const mutateIdentity = useIdentityMutation()
const hostedApplication = computed(() => typeof route.query.application === 'string' ? route.query.application : '')
const email = ref('')
const pending = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

async function submit() {
  pending.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    if (hostedApplication.value) {
      await mutateIdentity('/api/hosted-auth/password/forgot', {
        method: 'POST',
        body: { application: hostedApplication.value, email: email.value }
      })
    } else {
      await forgotPassword(email.value)
    }
    successMessage.value = 'If that account exists, password reset instructions have been sent.'
  } catch (error) {
    errorMessage.value = identityAuthErrorMessage(
      error,
      'We could not request a password reset.'
    )
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <IdentityAuthCard
    title="Reset your password"
    description="Enter your email address and we will send the reset instructions."
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
          v-model="email"
          type="email"
          autocomplete="email"
          required
        >
      </label>
      <button
        class="identity-auth-button"
        type="submit"
        :disabled="pending"
      >
        {{ pending ? 'Sending…' : 'Send reset instructions' }}
      </button>
    </form>
    <p class="identity-auth-links">
      <NuxtLink :to="{ path: '/auth/login', query: hostedApplication ? { application: hostedApplication, state: route.query.state } : {} }">
        Return to sign in
      </NuxtLink>
    </p>
  </IdentityAuthCard>
</template>
