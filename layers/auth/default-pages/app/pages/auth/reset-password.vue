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
const { resetPassword } = useIdentityAuth()
const mutateIdentity = useIdentityMutation()
const hostedClientId = computed(() => typeof route.query.client_id === 'string' ? route.query.client_id : '')
const schema = z.object({
  email: z.email('Enter a valid email address.'),
  token: z.string().min(64, 'Enter the reset token.'),
  password: z.string().min(12, 'Use at least 12 characters.'),
  passwordConfirmation: z.string().min(12, 'Confirm your password.')
}).refine(data => data.password === data.passwordConfirmation, {
  message: 'The password confirmation does not match.',
  path: ['passwordConfirmation']
})
type ResetPasswordSchema = z.output<typeof schema>
const fields = [
  { name: 'email', type: 'email' as const, label: 'Email', placeholder: 'you@example.com', required: true, autocomplete: 'email', defaultValue: typeof route.query.email === 'string' ? route.query.email : '' },
  { name: 'token', type: 'text' as const, label: 'Reset token', placeholder: 'Paste the token from your email', required: true, autocomplete: 'one-time-code', defaultValue: typeof route.query.token === 'string' ? route.query.token : '' },
  { name: 'password', type: 'password' as const, label: 'New password', placeholder: 'At least 12 characters', required: true, autocomplete: 'new-password' },
  { name: 'passwordConfirmation', type: 'password' as const, label: 'Confirm password', placeholder: 'Repeat your password', required: true, autocomplete: 'new-password' }
]
const pending = ref(false)
const successMessage = ref('')
const applicationUrl = ref('')

async function submit({ data }: FormSubmitEvent<ResetPasswordSchema>) {
  pending.value = true
  successMessage.value = ''

  try {
    if (hostedClientId.value) {
      const result = await mutateIdentity<{ applicationUrl: string }>('/api/hosted-auth/password/reset', {
        method: 'POST',
        body: { clientId: hostedClientId.value, ...data }
      })
      applicationUrl.value = result.applicationUrl
    } else {
      await resetPassword(data)
    }
    successMessage.value = 'Your password has been reset. You can now sign in.'
  } catch (error) {
    toast.add({
      title: 'Unable to reset your password',
      description: identityAuthErrorMessage(
        error,
        'We could not reset your password.'
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
    title="Choose a new password"
    description="Enter the reset token and a new password."
    icon="i-lucide-lock-keyhole"
    :submit="{ label: 'Reset password', loading: pending }"
    @submit="submit"
  >
    <template #header>
      <IdentityAuthFormHeader
        title="Choose a new password"
        description="Enter the reset token and a new password."
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
    </template>
  </UAuthForm>
</template>
