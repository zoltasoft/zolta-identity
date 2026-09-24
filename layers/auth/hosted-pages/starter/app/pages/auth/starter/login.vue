<script setup lang="ts">
import DefaultLoginPage from '#layers/auth/default-pages/app/pages/auth/login.vue'

definePageMeta({ layout: 'identity-auth', middleware: 'identity-live-auth' })
</script>

<template>
  <StarterAuthShell :page="DefaultLoginPage" />
</template>
