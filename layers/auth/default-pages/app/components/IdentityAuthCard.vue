<script setup lang="ts">
defineProps<{
  title: string
  description: string
}>()
</script>

<template>
  <section class="identity-auth-card">
    <IdentityAuthFormHeader
      :title="title"
      :description="description"
    />
    <slot />
  </section>
</template>
