<script setup lang="ts">
import type { DropdownMenuItem, NavigationMenuItem } from '@nuxt/ui'

const props = defineProps<{
  items: NavigationMenuItem[][]
  label: string
}>()

const navigationItems = computed(() => props.items.flat())
const currentItem = computed(() =>
  navigationItems.value.find(item => item.active) ?? navigationItems.value[0]
)
const mobileItems = computed<DropdownMenuItem[][]>(() => props.items.map(group =>
  group.map(item => ({
    label: item.label,
    icon: item.icon,
    to: item.to,
    trailingIcon: item.active ? 'i-lucide-check' : undefined
  }))
))
</script>

<template>
  <aside class="border-b border-default pb-5 lg:h-full lg:border-b-0 lg:border-e lg:pb-0 lg:pe-6 xl:pe-8">
    <div class="lg:sticky lg:top-6">
      <nav
        class="hidden lg:block"
        :aria-label="props.label"
      >
        <UNavigationMenu
          :items="props.items"
          orientation="vertical"
          color="neutral"
          variant="pill"
          :ui="{
            link: 'min-h-10 gap-2.5 px-3',
            linkLeadingIcon: 'size-4.5'
          }"
        />
      </nav>

      <UDropdownMenu
        :items="mobileItems"
        :content="{ align: 'start', collisionPadding: 16 }"
        :ui="{ content: 'min-w-64' }"
        class="lg:hidden"
      >
        <UButton
          :label="currentItem?.label"
          :icon="currentItem?.icon"
          trailing-icon="i-lucide-chevron-down"
          color="neutral"
          variant="outline"
          block
          class="justify-between bg-default"
          :aria-label="props.label"
        />
      </UDropdownMenu>
    </div>
  </aside>
</template>
