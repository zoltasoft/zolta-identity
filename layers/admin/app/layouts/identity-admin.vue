<script setup lang="ts">
const open = ref(false)
const sidebarCollapsed = useCookie<boolean>('identity-console-sidebar-collapsed', {
  default: () => true,
  sameSite: 'lax'
})

const localePath = useLocalePath()
const { t } = useI18n()

const identityAccess = useIdentityAccess()
const userSession = useUserSession()

const { data: identitySession } = await identityAccess.session()

if (!userSession.ready.value) {
  await userSession.fetch()
}

const { primaryLinks, secondaryLinks, searchGroups }
  = useIdentityAdminNavigation(identitySession, () => {
    open.value = false
  })
</script>

<template>
  <IdentityShellFrame
    v-model:open="open"
    v-model:collapsed="sidebarCollapsed"
    sidebar-id="identity-console-admin"
    :primary-links="primaryLinks"
    :secondary-links="secondaryLinks"
    :search-groups="searchGroups"
    :primary-navigation-label="
      t('identityConsole.accessibility.primaryNavigation')
    "
    :secondary-navigation-label="
      t('identityConsole.accessibility.secondaryNavigation')
    "
    :search-label="t('identityConsole.accessibility.search')"
  >
    <template #sidebar-header="{ collapsed }">
      <NuxtLink
        :to="localePath('/admin/projects')"
        class="flex min-w-0 items-center rounded-lg p-1 transition hover:bg-elevated focus-visible:outline-2 focus-visible:outline-primary"
        :class="collapsed ? 'justify-center' : undefined"
        :aria-label="t('identityConsole.brand')"
      >
        <AppLogo
          :collapsed="collapsed"
          :brand="t('identityConsole.brand')"
        />
      </NuxtLink>
    </template>

    <template #sidebar-start="{ collapsed }">
      <div class="space-y-3 pb-2">
        <UDashboardSearchButton
          :aria-label="t('identityConsole.accessibility.search')"
          :collapsed="collapsed"
          class="bg-transparent ring-default"
        />

        <UBadge
          v-if="!collapsed"
          color="primary"
          variant="soft"
          :label="
            identitySession?.projectName
              || t('identityConsole.administration')
          "
          class="mx-2"
        />
      </div>
    </template>

    <template #sidebar-footer="{ collapsed }">
      <IdentityUserMenu :collapsed="collapsed" />
    </template>

    <slot />
  </IdentityShellFrame>
</template>
