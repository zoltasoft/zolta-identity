<script setup lang="ts">
const open = ref(false)

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
    sidebar-id="identity-console-admin"
    :desktop-collapsed="true"
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
        class="flex min-w-0 items-center gap-3 rounded-lg p-2 transition hover:bg-elevated focus-visible:outline-2 focus-visible:outline-primary"
        :class="collapsed ? 'justify-center' : undefined"
        :aria-label="t('identityConsole.brand')"
      >
        <span
          class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary text-inverted shadow-sm"
        >
          <UIcon
            name="i-lucide-shield-check"
            class="size-5"
          />
        </span>

        <span
          v-if="!collapsed"
          class="truncate font-semibold tracking-tight text-highlighted"
        >
          {{ t('identityConsole.brand') }}
        </span>
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
