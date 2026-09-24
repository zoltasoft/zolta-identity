<script setup lang="ts">
import type { DropdownMenuItem } from '@nuxt/ui'

defineProps<{
  compact?: boolean
}>()

const route = useRoute()
const router = useRouter()
const switchLocalePath = useSwitchLocalePath()
const { locale, t } = useI18n()

const supportedLocales = [
  { code: 'en', labelKey: 'identityConsole.locales.en' },
  { code: 'fr', labelKey: 'identityConsole.locales.fr' }
] as const

type SupportedLocale = (typeof supportedLocales)[number]['code']

function switchLocale(code: SupportedLocale) {
  const path = switchLocalePath(code)
  if (!path) return

  const localizedRoute = router.resolve(path)
  return navigateTo({
    path: localizedRoute.path,
    query: route.query,
    hash: route.hash
  })
}

const localeOptions = computed<DropdownMenuItem[]>(() =>
  supportedLocales.map(entry => ({
    label: t(entry.labelKey),
    type: 'checkbox',
    checked: locale.value === entry.code,
    onUpdateChecked(checked: boolean) {
      if (checked) return switchLocale(entry.code)
    }
  }))
)

const languageItems = computed<DropdownMenuItem[][]>(() => [localeOptions.value])
</script>

<template>
  <div
    v-if="compact"
    class="flex items-center gap-1"
  >
    <UDropdownMenu
      :items="languageItems"
      :content="{ align: 'end', collisionPadding: 8 }"
      :ui="{ content: 'min-w-40' }"
    >
      <UTooltip :text="t('identityConsole.language')">
        <UButton
          icon="i-lucide-languages"
          color="neutral"
          variant="ghost"
          square
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

  <div
    v-else
    class="flex items-center gap-1"
  >
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
