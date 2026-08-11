<script setup lang="ts">
import type { ComputedRef } from 'vue'

defineProps<{
  title: string
  description: string
}>()

const config = useRuntimeConfig()
const brand = inject<ComputedRef<{
  name: string
  appearance: { welcomeText: string | null, logoUrl: string | null }
} | null>>('identity-auth-brand', computed(() => null))
const productName = computed(() => brand.value?.name ?? config.public.identityAuth.productName)
</script>

<template>
  <section class="identity-auth-card">
    <div class="identity-auth-brand">
      <img
        v-if="brand?.appearance.logoUrl"
        :src="brand.appearance.logoUrl"
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
      v-if="brand?.appearance.welcomeText"
      class="identity-auth-welcome"
    >
      {{ brand.appearance.welcomeText }}
    </p>
    <slot />
  </section>
</template>
