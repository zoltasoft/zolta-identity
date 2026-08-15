<script setup lang="ts">
type HostedAppearance = {
  welcomeText: string | null
  accentColor: string | null
  backgroundPreset: 'identity' | 'slate' | 'indigo' | 'emerald' | 'sunset'
  logoUrl: string | null
}

type HostedBrand = {
  key: string
  name: string
  appearance: HostedAppearance
  authentication: {
    termsUrl: string | null
    privacyUrl: string | null
  }
}

const route = useRoute()
const applicationKey = computed(() =>
  typeof route.query.application === 'string' ? route.query.application : ''
)
const clientId = computed(() =>
  typeof route.query.client_id === 'string' ? route.query.client_id : ''
)
const { data: experience } = await useAsyncData<HostedBrand | null>(
  'identity-auth-brand',
  async () => {
    if (!applicationKey.value && !clientId.value) return null
    const response = await $fetch<{ application: HostedBrand }>(
      '/api/hosted-auth/context',
      {
        query: applicationKey.value
          ? { application: applicationKey.value }
          : { clientId: clientId.value }
      }
    )
    return response.application
  },
  { watch: [applicationKey, clientId] }
)
const brand = computed(() => experience.value)
const config = useRuntimeConfig()
const productName = computed(
  () => brand.value?.name ?? config.public.identityAuth.productName
)
const backgroundPreset = computed(
  () => brand.value?.appearance.backgroundPreset ?? 'identity'
)
const brandStyle = computed(() =>
  brand.value?.appearance.accentColor
    ? { '--identity-auth-accent': brand.value.appearance.accentColor }
    : {}
)

provide('identity-auth-brand', brand)
</script>

<template>
  <main
    class="identity-auth-layout"
    :class="`identity-auth-layout--${backgroundPreset}`"
    :style="brandStyle"
  >
    <UHeader class="identity-auth-header">
      <template #left>
        <div class="identity-auth-header-brand">
          <span class="identity-auth-header-logo">
            <img
              v-if="brand?.appearance.logoUrl"
              :src="brand.appearance.logoUrl"
              :alt="`${productName} logo`"
            >
            <UIcon
              v-else
              name="i-lucide-app-window"
              class="size-5 text-muted"
            />
          </span>
          <span class="identity-auth-header-name">
            {{ productName }}
          </span>
        </div>
      </template>

      <template #right>
        <div class="identity-auth-header-controls">
          <IdentityConsoleControls compact />
        </div>
      </template>
    </UHeader>

    <div class="identity-auth-content">
      <slot />
      <IdentityAttribution
        :terms-url="brand?.authentication.termsUrl"
        :privacy-url="brand?.authentication.privacyUrl"
      />
    </div>
  </main>
</template>

<style>
:root {
  --identity-auth-bg: #f6f7fb;
  --identity-auth-card: #fff;
  --identity-auth-text: #172033;
  --identity-auth-muted: #59657b;
  --identity-auth-border: #e1e5ee;
  --identity-auth-input-border: #cdd3df;
  --identity-auth-status: #f1f4fb;
  --identity-auth-accent: #3157d5;
  color-scheme: light;
  font-family:
    Inter,
    ui-sans-serif,
    system-ui,
    -apple-system,
    BlinkMacSystemFont,
    "Segoe UI",
    sans-serif;
}

.dark {
  --identity-auth-bg: #0f172a;
  --identity-auth-card: #111827;
  --identity-auth-text: #f8fafc;
  --identity-auth-muted: #cbd5e1;
  --identity-auth-border: #334155;
  --identity-auth-input-border: #475569;
  --identity-auth-status: #1e293b;
  color-scheme: dark;
}

body {
  margin: 0;
  background: var(--identity-auth-bg);
  color: var(--identity-auth-text);
}

.identity-auth-layout {
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  position: relative;
}

.identity-auth-header {
  background: color-mix(
    in srgb,
    var(--identity-auth-card) 88%,
    transparent
  );
  border-bottom-color: var(--identity-auth-border);
  flex: none;
  width: 100%;
}

.identity-auth-header-brand {
  align-items: center;
  display: flex;
  gap: 0.75rem;
  max-width: min(70vw, 32rem);
  min-width: 0;
}

.identity-auth-header-logo {
  align-items: center;
  background: color-mix(in srgb, var(--identity-auth-card) 75%, transparent);
  border: 1px solid var(--identity-auth-border);
  border-radius: 0.75rem;
  display: inline-flex;
  flex: none;
  height: 2.5rem;
  justify-content: center;
  overflow: hidden;
  width: 2.5rem;
}

.identity-auth-header-logo img {
  border-radius: 9999px;
  height: 1.75rem;
  object-fit: contain;
  width: 1.75rem;
}

.identity-auth-header-name {
  color: var(--identity-auth-text);
  font-size: 1rem;
  font-weight: 650;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.identity-auth-header-controls {
  align-items: center;
  display: flex;
  flex: none;
  justify-content: flex-end;
}

.identity-auth-layout--slate {
  --identity-auth-bg: #e9eff7;
  background: linear-gradient(135deg, #e9eff7, #cad7e8);
}
.identity-auth-layout--indigo {
  --identity-auth-bg: #eef0ff;
  background: linear-gradient(135deg, #eef0ff, #d5dcff);
}
.identity-auth-layout--emerald {
  --identity-auth-bg: #e9f8f2;
  background: linear-gradient(135deg, #e9f8f2, #c7ecdc);
}
.identity-auth-layout--sunset {
  --identity-auth-bg: #fff2ea;
  background: linear-gradient(135deg, #fff2ea, #ffd9cd);
}

.identity-auth-page,
.identity-auth-form-shell {
  box-sizing: border-box;
  width: min(100%, 26rem);
}

.identity-auth-content {
  align-items: center;
  box-sizing: border-box;
  display: flex;
  flex: 1;
  flex-direction: column;
  justify-content: center;
  margin-inline: auto;
  padding: 2rem 1rem;
  width: min(100%, 28rem);
}

.identity-auth-content > * {
  margin-inline: auto;
}

.identity-auth-step-enter-active,
.identity-auth-step-leave-active {
  transition: opacity 180ms ease, transform 180ms ease;
}

.identity-auth-step-enter-from {
  opacity: 0;
  transform: translateX(1.25rem);
}

.identity-auth-step-leave-to {
  opacity: 0;
  transform: translateX(-1.25rem);
}

.identity-auth-form-shell {
  background: color-mix(
    in srgb,
    var(--identity-auth-card) 94%,
    var(--identity-auth-bg)
  );
  border: 1px solid color-mix(in srgb, var(--identity-auth-border) 78%, #fff);
  border-radius: 1rem;
  box-shadow: 0 0.75rem 2rem
    color-mix(in srgb, var(--identity-auth-text) 8%, transparent);
  padding: 1.75rem;
}

.identity-auth-form-logo {
  align-items: center;
  display: inline-flex;
  height: 2rem;
  justify-content: center;
  overflow: hidden;
  border-radius: 9999px;
  width: 2rem;
}

.identity-auth-form-logo img {
  border-radius: inherit;
  display: block;
  height: 100%;
  max-width: 100%;
  object-fit: contain;
  width: 100%;
}

.identity-auth-form-header {
  align-items: center;
  display: flex;
  flex-direction: column;
  text-align: center;
}

.identity-auth-form-header h1 {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0.5rem 0 0;
}

.identity-auth-form-header > p {
  color: var(--identity-auth-text);
  font-size: 1rem;
  font-weight: 600;
  margin: 0.5rem 0 0;
}

.identity-auth-form-header .identity-auth-form-header-description {
  color: var(--identity-auth-muted);
  font-size: 0.9rem;
  font-weight: 400;
  line-height: 1.5;
  margin-top: 0.25rem;
}

.identity-auth-form-header .identity-auth-form-header-link {
  color: var(--identity-auth-muted);
  font-size: 0.9rem;
  font-weight: 400;
  margin: 0.75rem 0 0;
}

.identity-auth-card {
  box-sizing: border-box;
  width: min(100%, 28rem);
  border: 1px solid var(--identity-auth-border);
  border-radius: 1rem;
  background: var(--identity-auth-card);
  box-shadow: 0 1rem 3rem rgba(23, 32, 51, 0.08);
  padding: 2rem;
}

.identity-auth-form {
  display: grid;
  gap: 1rem;
}

.identity-auth-field {
  display: grid;
  gap: 0.4rem;
  font-size: 0.9rem;
  font-weight: 650;
}

.identity-auth-field input {
  min-height: 2.75rem;
  box-sizing: border-box;
  border: 1px solid var(--identity-auth-input-border);
  background: var(--identity-auth-card);
  border-radius: 0.65rem;
  padding: 0.7rem 0.8rem;
  color: inherit;
  font: inherit;
  font-weight: 400;
}

.identity-auth-field input:focus {
  border-color: var(--identity-auth-accent);
  outline: 3px solid
    color-mix(in srgb, var(--identity-auth-accent) 18%, transparent);
}

.identity-auth-button {
  min-height: 2.75rem;
  border: 0;
  border-radius: 0.65rem;
  background: var(--identity-auth-accent);
  color: #fff;
  cursor: pointer;
  font: inherit;
  font-weight: 700;
}

.identity-auth-button:disabled {
  cursor: wait;
  opacity: 0.65;
}

.identity-auth-error,
.identity-auth-success,
.identity-auth-status {
  border-radius: 0.65rem;
  margin: 0;
  padding: 0.75rem;
  font-size: 0.9rem;
  line-height: 1.4;
}

.identity-auth-error {
  background: #fff0f0;
  color: #9f2525;
}

.identity-auth-success {
  background: #edf9f1;
  color: #22643a;
}

.identity-auth-status {
  background: var(--identity-auth-status);
  color: var(--identity-auth-muted);
}

.identity-auth-demo {
  display: grid;
  gap: 1rem;
  margin-top: 1.25rem;
  border-top: 1px solid var(--identity-auth-border);
  padding-top: 1.25rem;
}

.identity-auth-form + .identity-auth-demo {
  margin-top: 1.5rem;
}

.identity-auth-button--secondary {
  background: #172033;
}

.identity-auth-button--google {
  background: var(--identity-auth-card);
  border: 1px solid var(--identity-auth-input-border);
  color: var(--identity-auth-text);
}

.identity-auth-divider {
  color: var(--identity-auth-muted);
  font-size: 0.8rem;
  text-align: center;
}

.identity-auth-checkbox {
  align-items: flex-start;
  color: var(--identity-auth-muted);
  display: flex;
  font-size: 0.85rem;
  gap: 0.6rem;
  line-height: 1.45;
}

.identity-auth-checkbox input {
  margin-top: 0.2rem;
}
.identity-auth-checkbox a {
  color: var(--identity-auth-accent);
}

.identity-auth-expiry {
  margin: 0;
  color: var(--identity-auth-muted);
  font-size: 0.85rem;
  line-height: 1.45;
}

.identity-auth-field input[readonly] {
  background: var(--identity-auth-bg);
  color: var(--identity-auth-muted);
}

.identity-auth-links {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem 1rem;
  justify-content: center;
  margin: 1.25rem 0 0;
  font-size: 0.9rem;
}

.identity-auth-links a {
  color: #3157d5;
  text-decoration: none;
}

.identity-auth-links a:hover {
  text-decoration: underline;
}
</style>
