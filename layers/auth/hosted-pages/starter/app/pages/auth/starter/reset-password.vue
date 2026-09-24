<script setup lang="ts">
import DefaultResetPasswordPage from '#layers/auth/default-pages/app/pages/auth/reset-password.vue'

definePageMeta({ layout: 'identity-auth', middleware: 'identity-live-auth' })
</script>

<template>
  <StarterAuthShell :page="DefaultResetPasswordPage" />
</template>
