<script setup lang="ts">
import type { IdentityInstallationUser } from '#admin/types/identity-access'

definePageMeta({ layout: 'identity-admin', middleware: ['identity-system-admin'] })

const { users: fetchUsers, updateUser } = useIdentityAccess()
const { data: users, status, error, refresh } = await fetchUsers()

async function toggleAdmin(user: IdentityInstallationUser) {
  await updateUser(user.id, { is_system_admin: !user.is_system_admin, locked: user.locked })
  await refresh()
}

async function toggleLock(user: IdentityInstallationUser) {
  await updateUser(user.id, { is_system_admin: user.is_system_admin, locked: !user.locked })
  await refresh()
}
</script>

<template>
  <IdentityPanel
    panel-id="identity-installation-users"
    title="Installation users"
    description="Global identities are shared across project memberships. Project administrators remove memberships rather than deleting global accounts."
  >
    <IdentityShellState
      v-if="status === 'pending'"
      state="loading"
      title="Loading identities"
    />
    <IdentityShellState
      v-else-if="error"
      state="error"
      title="Unable to load identities"
      :description="error.statusMessage || 'The identity service did not return the installation users.'"
      @retry="refresh()"
    />
    <div
      v-else
      class="divide-y divide-default rounded-2xl border border-default px-5"
    >
      <div
        v-for="user in users"
        :key="user.id"
        class="flex flex-col gap-4 py-5 lg:flex-row lg:items-center lg:justify-between"
      >
        <div>
          <div class="flex flex-wrap items-center gap-2">
            <p class="font-medium">
              {{ user.username }}
            </p>
            <UBadge
              v-if="user.is_system_admin"
              color="primary"
              variant="soft"
            >
              System admin
            </UBadge>
            <UBadge
              v-if="user.locked"
              color="error"
              variant="soft"
            >
              Locked
            </UBadge>
          </div>
          <p class="text-sm text-muted">
            {{ user.email }} · {{ user.project_count }} project{{ user.project_count === 1 ? '' : 's' }}
          </p>
        </div>
        <div class="flex gap-2">
          <UButton
            :label="user.is_system_admin ? 'Remove system admin' : 'Make system admin'"
            color="neutral"
            variant="outline"
            icon="i-lucide-shield-user"
            @click="toggleAdmin(user)"
          />
          <UButton
            :label="user.locked ? 'Unlock' : 'Lock'"
            :color="user.locked ? 'success' : 'error'"
            variant="soft"
            :icon="user.locked ? 'i-lucide-lock-open' : 'i-lucide-lock'"
            @click="toggleLock(user)"
          />
        </div>
      </div>
    </div>
  </IdentityPanel>
</template>
