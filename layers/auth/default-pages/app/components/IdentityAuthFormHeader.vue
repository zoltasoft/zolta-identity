<script setup lang="ts">
import type { ComputedRef } from 'vue'

defineProps<{
  title: string
  description?: string
}>()

const brand = inject<ComputedRef<{
  appearance: { logoUrl: string | null }
} | null>>('identity-auth-brand', computed(() => null))
</script>

<template>
  <div class="identity-auth-form-header">
    <span
      v-if="brand?.appearance.logoUrl"
      class="identity-auth-form-logo"
    >
      <img
        :src="brand.appearance.logoUrl"
        alt=""
      >
    </span>
    <UIcon
      v-else
      name="i-lucide-app-window"
      class="size-8 text-primary"
    />
    <h1>
      {{ title }}
    </h1>
    <p
      v-if="description"
      class="identity-auth-form-header-description"
    >
      {{ description }}
    </p>
    <slot />
  </div>
</template>
