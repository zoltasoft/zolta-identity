<script setup lang="ts">
import { useIdentityMutation } from '../../../app/composables/useIdentityMutation'

type HostedBrand = {
  name: string
  returnUrl: string
  appearance: { logoUrl: string | null }
  authentication?: { termsUrl?: string | null, privacyUrl?: string | null }
}

const route = useRoute()
const mutate = useIdentityMutation()
const applicationKey = computed(() =>
  typeof route.query.application === 'string' ? route.query.application : ''
)
const { data: brand } = await useAsyncData<HostedBrand | null>(
  'identity-account-brand',
  async () => {
    if (!applicationKey.value) return null
    const response = await $fetch<{ application: HostedBrand }>(
      '/api/hosted-auth/context',
      {
        query: { application: applicationKey.value }
      }
    )
    return response.application
  },
  { watch: [applicationKey] }
)
const loggingOut = ref(false)

async function logout() {
  if (loggingOut.value) return
  loggingOut.value = true
  try {
    await mutate('/api/hosted-account/logout', { method: 'POST' })
    const destination = new URL(`/api/identity/${encodeURIComponent(applicationKey.value)}/account/logout`, new URL(brand.value?.returnUrl ?? '/', window.location.origin).origin)
    await navigateTo(destination.toString(), { external: true })
  } catch (error) {
    loggingOut.value = false
    throw error
  }
}
</script>

<template>
  <div class="flex min-h-screen flex-col bg-default text-default">
    <div
      v-if="loggingOut"
      class="fixed inset-0 z-50 grid place-items-center bg-default/85 px-4 backdrop-blur-sm"
      role="status"
      aria-live="polite"
      aria-busy="true"
    >
      <div class="flex w-full max-w-sm flex-col items-center gap-3 rounded-2xl border border-default bg-default p-8 text-center shadow-xl">
        <UIcon
          name="i-lucide-loader-circle"
          class="size-7 animate-spin text-primary"
        />
        <p class="m-0 font-medium text-highlighted">
          Signing you out…
        </p>
        <p class="m-0 text-sm text-muted">
          Your account session is being closed securely.
        </p>
      </div>
    </div>

    <UHeader class="border-b border-default bg-default">
      <template #left>
        <a
          :href="brand?.returnUrl ?? '/'"
          class="flex min-w-0 items-center gap-3"
        >
          <span
            class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-default bg-elevated"
          >
            <img
              v-if="brand?.appearance.logoUrl"
              :src="brand.appearance.logoUrl"
              :alt="`${brand.name} logo`"
              class="size-7 rounded-full object-contain"
            >
            <UIcon
              v-else
              name="i-lucide-app-window"
              class="size-5 text-muted"
            />
          </span>
          <span
            class="truncate text-sm font-semibold text-highlighted sm:text-base"
          >
            {{ brand?.name ?? "Application" }}
          </span>
        </a>
      </template>

      <template #right>
        <div class="flex shrink-0 items-center gap-2">
          <UButtonGroup>
            <UButton
              icon="i-lucide-house"
              color="neutral"
              variant="ghost"
              :href="brand?.returnUrl ?? '/'"
              class="sm:hidden"
              aria-label="Go home"
            />
            <UButton
              label="Home"
              icon="i-lucide-house"
              color="neutral"
              variant="ghost"
              :href="brand?.returnUrl ?? '/'"
              class="hidden sm:inline-flex"
            />
          </UButtonGroup>

          <div class="rounded-lg border border-default bg-elevated/40 px-1 py-1">
            <IdentityConsoleControls />
          </div>

          <UTooltip text="End session">
            <UButton
              icon="i-lucide-log-out"
              color="error"
              variant="soft"
              :loading="loggingOut"
              class="sm:hidden"
              aria-label="Log out"
              @click="logout"
            />
          </UTooltip>
          <UButton
            label="Log out"
            icon="i-lucide-log-out"
            color="error"
            variant="soft"
            :loading="loggingOut"
            class="hidden sm:inline-flex"
            @click="logout"
          />
        </div>
      </template>
    </UHeader>

    <slot />

    <footer
      v-if="
        brand?.authentication?.termsUrl || brand?.authentication?.privacyUrl
      "
      class="border-t border-default"
    >
      <UContainer
        class="flex flex-wrap items-center gap-x-3 gap-y-1 py-4 text-sm text-muted"
      >
        <a
          v-if="brand.authentication.termsUrl"
          :href="brand.authentication.termsUrl"
          target="_blank"
          rel="noopener"
          class="hover:text-default"
        >Terms of Service</a>
        <span
          v-if="
            brand.authentication.termsUrl && brand.authentication.privacyUrl
          "
          aria-hidden="true"
        >·</span>
        <a
          v-if="brand.authentication.privacyUrl"
          :href="brand.authentication.privacyUrl"
          target="_blank"
          rel="noopener"
          class="hover:text-default"
        >Privacy Policy</a>
      </UContainer>
    </footer>

    <IdentityAttribution />
  </div>
</template>
