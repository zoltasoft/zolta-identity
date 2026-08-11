<script setup lang="ts">
import { useIdentityMutation } from '../../../../app/composables/useIdentityMutation'

definePageMeta({
  layout: 'identity-auth',
  middleware: 'identity-live-auth'
})

const config = useRuntimeConfig()
const route = useRoute()
const { register } = useIdentityAuth()
const mutateIdentity = useIdentityMutation()
const hostedApplication = computed(() => typeof route.query.application === 'string' ? route.query.application : '')
const hostedState = computed(() => typeof route.query.state === 'string' ? route.query.state : '')
const hosted = computed(() => Boolean(hostedApplication.value && hostedState.value))
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
    if (hosted.value) {
      const result = await mutateIdentity<{ redirectUrl?: string }>('/api/hosted-auth/register', {
        method: 'POST',
        body: {
          application: hostedApplication.value,
          state: hostedState.value,
          ...form
        }
      })
      if (result.redirectUrl) {
        await navigateTo(result.redirectUrl, { external: true })
        return
      }
      await navigateTo({
        path: '/auth/verify-email',
        query: { application: hostedApplication.value, state: hostedState.value }
      })
      return
    }

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
      <NuxtLink :to="{ path: '/auth/login', query: hosted ? { application: hostedApplication, state: hostedState } : {} }">
        Already have an account?
      </NuxtLink>
    </p>
  </IdentityAuthCard>
</template>
