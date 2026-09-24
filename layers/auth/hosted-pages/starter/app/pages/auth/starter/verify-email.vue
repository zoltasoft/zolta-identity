<script setup lang="ts">
import DefaultVerifyEmailPage from '#layers/auth/default-pages/app/pages/auth/verify-email.vue'

definePageMeta({ layout: 'identity-auth', middleware: 'identity-live-auth' })
</script>

<template>
  <StarterAuthShell :page="DefaultVerifyEmailPage" />
</template>
