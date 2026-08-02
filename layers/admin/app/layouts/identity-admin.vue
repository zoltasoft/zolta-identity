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

const { primaryLinks, secondaryLinks } = useIdentityAdminNavigation(
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
    :primary-links="primaryLinks"
    :secondary-links="secondaryLinks"
    primary-navigation-label="Identity administration"
    secondary-navigation-label="Application navigation"
  >
    <template #sidebar-header="{ collapsed }">
      <UButton
        :to="localePath('/admin/projects')"
        :label="collapsed ? '' : 'Identity Console'"
        icon="i-lucide-shield-check"
        size="md"
        color="neutral"
        variant="ghost"
      />
    </template>

    <template #sidebar-start>
      <div class="px-2 pb-2">
        <UBadge
          color="primary"
          variant="soft"
          :label="identitySession?.projectName || 'Administration'"
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
