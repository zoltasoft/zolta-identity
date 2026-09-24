<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

const props = defineProps<{
  items: NavigationMenuItem[][]
  label: string
}>()
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

      <nav
        class="lg:hidden"
        :aria-label="props.label"
      >
        <UNavigationMenu
          :items="props.items"
          orientation="horizontal"
          color="neutral"
          variant="pill"
          :ui="{
            root: 'w-full',
            list: 'w-full justify-start gap-1',
            link: 'min-h-10 gap-2 px-3',
            linkLeadingIcon: 'size-4.5'
          }"
        />
      </nav>
    </div>
  </aside>
</template>
