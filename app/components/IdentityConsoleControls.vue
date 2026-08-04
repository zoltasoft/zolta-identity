<script setup lang="ts">
import type { DropdownMenuItem } from '@nuxt/ui'

const route = useRoute()
const switchLocalePath = useSwitchLocalePath()
const { locale, t } = useI18n()

const supportedLocales = [
  { code: 'en', labelKey: 'identityConsole.locales.en' },
  { code: 'fr', labelKey: 'identityConsole.locales.fr' }
] as const

type SupportedLocale = (typeof supportedLocales)[number]['code']

const languageItems = computed<DropdownMenuItem[][]>(() => [
  supportedLocales.map(entry => ({
    label: t(entry.labelKey),
    type: 'checkbox',
    checked: locale.value === entry.code,
    onUpdateChecked(checked: boolean) {
      if (!checked) return

      const path = switchLocalePath(entry.code as SupportedLocale)
      if (path && path !== route.fullPath) {
        return navigateTo(path)
      }
    }
  }))
])
</script>

<template>
  <div class="flex items-center gap-1">
    <UDropdownMenu
      :items="languageItems"
      :content="{ align: 'end', collisionPadding: 8 }"
      :ui="{ content: 'min-w-40' }"
    >
      <UTooltip :text="t('identityConsole.language')">
        <UButton
          :label="locale.toUpperCase()"
          icon="i-lucide-languages"
          trailing-icon="i-lucide-chevron-down"
          color="neutral"
          variant="ghost"
          :aria-label="t('identityConsole.language')"
        />
      </UTooltip>
    </UDropdownMenu>

    <UTooltip :text="t('identityConsole.appearance')">
      <UColorModeButton
        color="neutral"
        variant="ghost"
        :aria-label="t('identityConsole.appearance')"
      />
    </UTooltip>
  </div>
</template>
