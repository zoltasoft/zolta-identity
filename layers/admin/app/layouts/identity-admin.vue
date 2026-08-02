<script setup lang="ts">
const open = ref(false)
const signingOut = ref(false)
const localePath = useLocalePath()
const identityAccess = useIdentityAccess()
const userSession = useUserSession()
const { data: identitySession } = await identityAccess.session()

if (!userSession.ready.value) {
  await userSession.fetch()
}

const { primaryLinks, secondaryLinks, searchGroups } = useIdentityAdminNavigation(
  identitySession,
  () => {
    open.value = false
  }
)

const user = computed(() => userSession.user.value as {
  name?: string
  email?: string
} | null)

async function signOut() {
  if (signingOut.value) return
  signingOut.value = true

  try {
    await identityAccess.logout()
  } finally {
    await userSession.fetch()
    await navigateTo(localePath('/admin/sign-in'))
    signingOut.value = false
  }
}
</script>

<template>
  <IdentityShellFrame
    v-model:open="open"
    sidebar-id="identity-console-admin"
    :desktop-collapsed="true"
    :primary-links="primaryLinks"
    :secondary-links="secondaryLinks"
    :search-groups="searchGroups"
    primary-navigation-label="Identity administration"
    secondary-navigation-label="Application navigation"
  >
    <template #sidebar-header="{ collapsed }">
      <NuxtLink
        :to="localePath('/admin/projects')"
        class="flex min-w-0 items-center gap-3 rounded-lg p-2 transition hover:bg-elevated focus-visible:outline-2 focus-visible:outline-primary"
        aria-label="Identity Console"
      >
        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary text-inverted shadow-sm">
          <UIcon
            name="i-lucide-shield-check"
            class="size-5"
          />
        </span>
        <span
          v-if="!collapsed"
          class="truncate font-semibold tracking-tight text-highlighted"
        >Identity Console</span>
      </NuxtLink>
    </template>

    <template #sidebar-start="{ collapsed }">
      <div class="space-y-3 pb-2">
        <UDashboardSearchButton
          aria-label="Search Identity Console"
          :collapsed="collapsed"
          class="bg-transparent ring-default"
        />
        <UBadge
          v-if="!collapsed"
          color="primary"
          variant="soft"
          :label="identitySession?.projectName || 'Administration'"
          class="mx-2"
        />
      </div>
    </template>

    <template #sidebar-footer="{ collapsed }">
      <div class="flex min-w-0 items-center gap-2 p-2">
        <div
          v-if="!collapsed"
          class="min-w-0 flex-1"
        >
          <p class="truncate text-sm font-medium text-highlighted">
            {{ user?.name || 'Administrator' }}
          </p>
          <p class="truncate text-xs text-muted">
            {{ user?.email }}
          </p>
        </div>
        <UButton
          :label="collapsed ? undefined : 'Sign out'"
          icon="i-lucide-log-out"
          color="neutral"
          variant="ghost"
          :loading="signingOut"
          :aria-label="collapsed ? 'Sign out' : undefined"
          @click="signOut"
        />
      </div>
    </template>

    <slot />
  </IdentityShellFrame>
</template>
