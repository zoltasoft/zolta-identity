<script setup lang="ts">
import type { ComputedRef } from 'vue'

defineProps<{
  title: string
  description: string
}>()

const config = useRuntimeConfig()
const brand = inject<ComputedRef<{
  name: string
  appearance: { welcome_text: string | null, logo_url: string | null }
} | null>>('identity-auth-brand', computed(() => null))
const productName = computed(() => brand.value?.name ?? config.public.identityAuth.productName)
</script>

<template>
  <section class="identity-auth-card">
    <div class="identity-auth-brand">
      <img
        v-if="brand?.appearance.logo_url"
        :src="brand.appearance.logo_url"
        :alt="`${productName} logo`"
        class="identity-auth-logo"
      >
      <p class="identity-auth-eyebrow">
        {{ productName }}
      </p>
    </div>
    <h1>{{ title }}</h1>
    <p class="identity-auth-intro">
      {{ description }}
    </p>
    <p
      v-if="brand?.appearance.welcome_text"
      class="identity-auth-welcome"
    >
      {{ brand.appearance.welcome_text }}
    </p>
    <slot />
  </section>
</template>
