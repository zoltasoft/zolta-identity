<script setup lang="ts">
defineProps<{
  title: string
  description: string
}>()

const config = useRuntimeConfig()
</script>

<template>
  <section class="identity-auth-card">
    <p class="identity-auth-eyebrow">
      {{ config.public.identityAuth.productName }}
    </p>
    <h1>{{ title }}</h1>
    <p class="identity-auth-intro">
      {{ description }}
    </p>
    <slot />
  </section>
</template>
