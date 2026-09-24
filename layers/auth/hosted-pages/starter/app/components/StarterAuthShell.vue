<script setup lang="ts">
import type { Component } from 'vue'

const { page } = defineProps<{
  page: Component
}>()

provide('identity-auth-page-set', 'starter')
</script>

<template>
  <component :is="page" />
</template>
