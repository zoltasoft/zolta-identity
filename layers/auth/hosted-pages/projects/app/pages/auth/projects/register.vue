<script setup lang="ts">
import DefaultRegisterPage from '#layers/auth/default-pages/app/pages/auth/register.vue'

definePageMeta({ layout: 'identity-auth', middleware: 'identity-live-auth' })
</script>

<template>
  <ProjectsAuthShell :page="DefaultRegisterPage" />
</template>
