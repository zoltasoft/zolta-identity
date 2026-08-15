<script setup lang="ts">
import * as z from 'zod/v4'
import type { FormSubmitEvent } from '@nuxt/ui'
import { useIdentityMutation } from '../../../../app/composables/useIdentityMutation'

definePageMeta({
  layout: 'identity-auth',
  middleware: 'identity-live-auth'
})

const route = useRoute()
const toast = useToast()
const { forgotPassword } = useIdentityAuth()
const mutateIdentity = useIdentityMutation()
const hostedApplication = computed(() => typeof route.query.application === 'string' ? route.query.application : '')
const schema = z.object({ email: z.email('Enter a valid email address.') })
type ForgotPasswordSchema = z.output<typeof schema>
const fields = [{ name: 'email', type: 'email' as const, label: 'Email', placeholder: 'you@example.com', required: true, autocomplete: 'email' }]
const pending = ref(false)
const successMessage = ref('')

async function submit({ data }: FormSubmitEvent<ForgotPasswordSchema>) {
  pending.value = true
  successMessage.value = ''

  try {
    if (hostedApplication.value) {
      await mutateIdentity('/api/hosted-auth/password/forgot', {
        method: 'POST',
        body: { application: hostedApplication.value, email: data.email }
      })
    } else {
      await forgotPassword(data.email)
    }
    successMessage.value = 'If that account exists, password reset instructions have been sent.'
  } catch (error) {
    toast.add({
      title: 'Unable to request a password reset',
      description: identityAuthErrorMessage(
        error,
        'We could not request a password reset.'
      ),
      color: 'error'
    })
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <UAuthForm
    class="identity-auth-form-shell"
    :fields="fields"
    :schema="schema"
    :validate-on="['input']"
    title="Reset your password"
    description="Enter your email address and we will send the reset instructions."
    icon="i-lucide-key-round"
    :submit="{ label: 'Send reset instructions', loading: pending }"
    @submit="submit"
  >
    <template #header>
      <IdentityAuthFormHeader
        title="Reset your password"
        description="Enter your email address and we will send the reset instructions."
      />
    </template>
    <template #validation>
      <p
        v-if="successMessage"
        class="identity-auth-success"
      >
        {{ successMessage }}
      </p>
    </template>
    <template #footer>
      <NuxtLink :to="{ path: '/auth/login', query: hostedApplication ? { application: hostedApplication, state: route.query.state } : {} }">
        Return to sign in
      </NuxtLink>
    </template>
  </UAuthForm>
</template>
