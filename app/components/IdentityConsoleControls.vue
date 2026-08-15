<script setup lang="ts">
import type { DropdownMenuItem } from '@nuxt/ui'

defineProps<{
  compact?: boolean
}>()

const route = useRoute()
const router = useRouter()
const switchLocalePath = useSwitchLocalePath()
const { locale, t } = useI18n()
const colorMode = useColorMode()

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
const compactItems = computed<DropdownMenuItem[][]>(() => [[
  {
    label: t('identityConsole.language'),
    icon: 'i-lucide-languages',
    children: localeOptions.value
  },
  {
    label: t('identityConsole.appearance'),
    icon: 'i-lucide-sun-moon',
    children: [
      {
        label: t('identityConsole.light'),
        icon: 'i-lucide-sun',
        type: 'checkbox',
        checked: colorMode.value === 'light',
        onUpdateChecked(checked: boolean) {
          if (checked) colorMode.preference = 'light'
        }
      },
      {
        label: t('identityConsole.dark'),
        icon: 'i-lucide-moon',
        type: 'checkbox',
        checked: colorMode.value === 'dark',
        onUpdateChecked(checked: boolean) {
          if (checked) colorMode.preference = 'dark'
        }
      }
    ]
  }
]])
</script>

<template>
  <UDropdownMenu
    v-if="compact"
    :items="compactItems"
    :content="{ align: 'end', collisionPadding: 8 }"
    :ui="{ content: 'min-w-48' }"
  >
    <UTooltip :text="t('identityConsole.configuration')">
      <UButton
        icon="i-lucide-settings"
        color="neutral"
        variant="ghost"
        :aria-label="t('identityConsole.configuration')"
      />
    </UTooltip>
  </UDropdownMenu>

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
