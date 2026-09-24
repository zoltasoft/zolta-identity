<script setup lang="ts">
import DefaultForgotPasswordPage from '#layers/auth/default-pages/app/pages/auth/forgot-password.vue'

definePageMeta({ layout: 'identity-auth', middleware: 'identity-live-auth' })
</script>

<template>
  <StarterAuthShell :page="DefaultForgotPasswordPage" />
</template>
