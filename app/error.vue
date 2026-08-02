<script setup lang="ts">
import type { NuxtError } from '#app'

const props = defineProps<{
  error: NuxtError
}>()

const { locale, t } = useI18n()
const localePath = useLocalePath()
const session = useUserSession()
const statusCode = computed(() => props.error.statusCode || 500)
const isNotFound = computed(() => statusCode.value === 404)
const title = computed(() => isNotFound.value
  ? t('error.notFoundTitle')
  : t('error.genericTitle'))
const description = computed(() => isNotFound.value
  ? t('error.notFoundDescription')
  : t('error.genericDescription'))
const recoveryPath = computed(() => session.loggedIn.value
  ? localePath('/dashboard')
  : localePath('/saas'))

useHead({
  htmlAttrs: { lang: locale }
})

useSeoMeta({
  title,
  description
})

async function recover() {
  await clearError({ redirect: recoveryPath.value })
}

function reload() {
  reloadNuxtApp()
}
</script>

<template>
  <UApp>
    <main class="min-h-screen flex items-center justify-center px-6 py-16">
      <UPageCard
        variant="subtle"
        class="w-full max-w-xl"
      >
        <div class="flex flex-col items-center text-center gap-6 py-6">
          <AppLogo class="h-8 w-auto" />

          <div class="space-y-3">
            <p class="text-sm font-semibold text-primary">
              {{ statusCode }}
            </p>
            <h1 class="text-2xl sm:text-3xl font-semibold text-highlighted">
              {{ title }}
            </h1>
            <p class="text-muted max-w-md">
              {{ description }}
            </p>
          </div>

          <div class="flex flex-wrap justify-center gap-3">
            <UButton
              icon="i-lucide-house"
              :label="t('error.backHome')"
              @click="recover"
            />
            <UButton
              icon="i-lucide-rotate-ccw"
              :label="t('error.tryAgain')"
              color="neutral"
              variant="outline"
              @click="reload"
            />
          </div>
        </div>
      </UPageCard>
    </main>
  </UApp>
</template>
