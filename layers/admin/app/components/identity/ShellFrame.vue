<script setup lang="ts">
import type {
  CommandPaletteGroup,
  CommandPaletteItem,
  NavigationMenuItem
} from '@nuxt/ui'

const props = withDefaults(defineProps<{
  sidebarId: string
  primaryLinks: NavigationMenuItem[][]
  secondaryLinks?: NavigationMenuItem[][]
  searchGroups?: CommandPaletteGroup<CommandPaletteItem>[]
  unit?: 'rem' | 'px'
  collapsible?: boolean
  resizable?: boolean
  collapsedSize?: number
  desktopCollapsed?: boolean
  primaryNavigationUi?: Record<string, string>
  secondaryNavigationUi?: Record<string, string>
  primaryNavigationClass?: string
  secondaryNavigationClass?: string
  primaryNavigationLabel?: string
  secondaryNavigationLabel?: string
  searchLabel?: string
}>(), {
  secondaryLinks: () => [],
  searchGroups: () => [],
  unit: 'rem',
  collapsible: true,
  resizable: false,
  collapsedSize: 4,
  desktopCollapsed: false,
  primaryNavigationUi: undefined,
  secondaryNavigationUi: undefined,
  primaryNavigationClass: '',
  secondaryNavigationClass: 'mt-auto',
  primaryNavigationLabel: 'Primary navigation',
  secondaryNavigationLabel: 'Secondary navigation',
  searchLabel: 'Search dashboard'
})

const open = defineModel<boolean>('open', { default: false })

const isDesktop = useMediaQuery('(min-width: 1024px)')
const collapsed = computed(() => props.desktopCollapsed)

watch(isDesktop, (desktop) => {
  // Avoid persisting mobile drawer state when switching to desktop.
  if (desktop && open.value) {
    open.value = false
  }
})
</script>

<template>
  <UDashboardGroup :unit="props.unit">
    <UDashboardSidebar
      :id="props.sidebarId"
      v-model:open="open"
      :collapsible="props.collapsible"
      :collapsed="collapsed"
      :collapsed-size="props.collapsedSize"
      :resizable="props.resizable"
      :ui="{ footer: 'lg:border-t lg:border-default' }"
    >
      <template #header="{ collapsed: sidebarCollapsed }">
        <slot
          name="sidebar-header"
          :collapsed="sidebarCollapsed"
        />
      </template>

      <template #default="{ collapsed: sidebarCollapsed }">
        <slot
          name="sidebar-start"
          :collapsed="sidebarCollapsed"
        >
          <UDashboardSearchButton
            :aria-label="props.searchLabel"
            :collapsed="sidebarCollapsed"
            class="bg-transparent ring-default"
          />
        </slot>

        <nav :aria-label="props.primaryNavigationLabel">
          <UNavigationMenu
            :collapsed="sidebarCollapsed"
            :items="props.primaryLinks"
            orientation="vertical"
            tooltip
            popover
            :class="props.primaryNavigationClass"
            :ui="props.primaryNavigationUi"
          />
        </nav>

        <nav
          v-if="props.secondaryLinks.length > 0"
          :class="props.secondaryNavigationClass"
          :aria-label="props.secondaryNavigationLabel"
        >
          <UNavigationMenu
            :collapsed="sidebarCollapsed"
            :items="props.secondaryLinks"
            orientation="vertical"
            tooltip
            :ui="props.secondaryNavigationUi"
          />
        </nav>
      </template>

      <template #footer="{ collapsed: sidebarCollapsed }">
        <slot
          name="sidebar-footer"
          :collapsed="sidebarCollapsed"
        >
          <DashboardUserMenu :collapsed="sidebarCollapsed" />
        </slot>
      </template>
    </UDashboardSidebar>

    <UDashboardSearch
      v-if="props.searchGroups.length > 0"
      :aria-label="props.searchLabel"
      :groups="props.searchGroups"
    />

    <slot name="workspace">
      <slot />
    </slot>

    <slot name="global-feedback" />
  </UDashboardGroup>
</template>
