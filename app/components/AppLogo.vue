<script setup lang="ts">
import starterLogo from '../../assets/branding/zoltasoft-starter.png'

withDefaults(defineProps<{
  collapsed?: boolean
  brand?: string
  logo?: string
}>(), {
  collapsed: false,
  brand: 'Zoltasoft Starter',
  logo: starterLogo
})
</script>

<template>
  <span class="inline-flex min-w-0 items-center gap-2 text-highlighted">
    <img
      :src="logo"
      alt=""
      aria-hidden="true"
      class="size-8 shrink-0 object-contain"
    >
    <span
      v-if="!collapsed"
      class="truncate text-sm font-semibold tracking-tight"
    >{{ brand }}</span>
  </span>
</template>
