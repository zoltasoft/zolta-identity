<script setup lang="ts">
import type { TableColumn } from '@nuxt/ui'
import type { IdentityInstallationUser } from '#admin/types/identity-access'

definePageMeta({ layout: 'identity-admin', middleware: ['identity-system-admin'] })

const { users: fetchUsers, updateUser } = useIdentityAccess()
const { data: users, status, error, refresh } = await fetchUsers()
const accountState = ref<'all' | 'active' | 'locked' | 'unverified'>('all')
const updatingId = ref<string | null>(null)
const scopedUsers = computed(() => (users.value ?? []).filter((user) => {
  if (accountState.value === 'locked') return user.locked
  if (accountState.value === 'active') return !user.locked
  if (accountState.value === 'unverified') return !user.email_verified_at
  return true
}))
const collection = useIdentityCollection(scopedUsers, user => (
  `${user.username} ${user.email} ${user.is_system_admin ? 'system admin' : ''} ${user.locked ? 'locked' : ''}`
))

const columns: TableColumn<IdentityInstallationUser>[] = [
  { accessorKey: 'username', header: 'Identity' },
  { accessorKey: 'email_verified_at', header: 'Verification' },
  { accessorKey: 'project_count', header: 'Projects' },
  { accessorKey: 'is_system_admin', header: 'Access' },
  { accessorKey: 'created_at', header: 'Created' },
  { id: 'actions', header: '' }
]

watch(accountState, () => {
  collection.page.value = 1
})

async function update(user: IdentityInstallationUser, changes: Partial<Pick<IdentityInstallationUser, 'is_system_admin' | 'locked'>>) {
  updatingId.value = user.id
  try {
    await updateUser(user.id, {
      is_system_admin: changes.is_system_admin ?? user.is_system_admin,
      locked: changes.locked ?? user.locked
    })
    await refresh()
  } finally {
    updatingId.value = null
  }
}
</script>

<template>
  <IdentityPanel
    panel-id="identity-installation-users"
    title="Installation users"
    icon="i-lucide-users"
    description="Review global identities and installation-level security. Application access remains isolated in project memberships."
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

    <template v-else>
      <IdentityCollectionToolbar
        v-model="collection.search.value"
        placeholder="Search users or email"
        :result-count="collection.total.value"
      >
        <template #filters>
          <USelect
            v-model="accountState"
            :items="[
              { label: 'All accounts', value: 'all' },
              { label: 'Active', value: 'active' },
              { label: 'Locked', value: 'locked' },
              { label: 'Unverified', value: 'unverified' }
            ]"
            size="lg"
            class="w-full sm:w-44"
          />
        </template>
      </IdentityCollectionToolbar>

      <IdentityTableCard
        title="User directory"
        description="Manage installation administrators and account locks."
        :count="collection.total.value"
      >
        <UTable
          :data="collection.paginatedItems.value"
          :columns="columns"
          empty="No identities match the current filters."
          class="min-w-5xl"
          :ui="{
            thead: '[&>tr]:bg-elevated/50 [&>tr]:after:content-none',
            tbody: '[&>tr]:last:[&>td]:border-b-0',
            th: 'border-b border-default',
            td: 'border-b border-default'
          }"
        >
          <template #username-cell="{ row }">
            <div class="flex min-w-0 items-center gap-3 py-1">
              <UAvatar
                :alt="row.original.username"
                size="sm"
              />
              <div class="min-w-0">
                <p class="truncate font-medium text-highlighted">
                  {{ row.original.username }}
                </p>
                <p class="truncate text-xs text-muted">
                  {{ row.original.email }}
                </p>
              </div>
            </div>
          </template>
          <template #email_verified_at-cell="{ row }">
            <UBadge
              :color="row.original.email_verified_at ? 'success' : 'warning'"
              variant="soft"
            >
              {{ row.original.email_verified_at ? 'Verified' : 'Pending' }}
            </UBadge>
          </template>
          <template #project_count-cell="{ row }">
            <span class="tabular-nums">{{ row.original.project_count }}</span>
          </template>
          <template #is_system_admin-cell="{ row }">
            <div class="flex flex-wrap gap-2">
              <UBadge
                v-if="row.original.is_system_admin"
                color="primary"
                variant="soft"
              >
                System admin
              </UBadge>
              <UBadge
                v-if="row.original.locked"
                color="error"
                variant="soft"
              >
                Locked
              </UBadge>
              <span
                v-if="!row.original.is_system_admin && !row.original.locked"
                class="text-sm text-muted"
              >Member</span>
            </div>
          </template>
          <template #created_at-cell="{ row }">
            <span class="whitespace-nowrap text-sm text-muted">
              {{ formatIdentityDate(row.original.created_at) }}
            </span>
          </template>
          <template #actions-cell="{ row }">
            <div class="flex justify-end gap-1">
              <UTooltip :text="row.original.is_system_admin ? 'Remove system admin' : 'Make system admin'">
                <UButton
                  :icon="row.original.is_system_admin ? 'i-lucide-shield-off' : 'i-lucide-shield-plus'"
                  color="neutral"
                  variant="ghost"
                  :loading="updatingId === row.original.id"
                  :aria-label="row.original.is_system_admin ? 'Remove system admin' : 'Make system admin'"
                  @click="update(row.original, { is_system_admin: !row.original.is_system_admin })"
                />
              </UTooltip>
              <UTooltip :text="row.original.locked ? 'Unlock account' : 'Lock account'">
                <UButton
                  :icon="row.original.locked ? 'i-lucide-lock-open' : 'i-lucide-lock'"
                  :color="row.original.locked ? 'success' : 'error'"
                  variant="ghost"
                  :loading="updatingId === row.original.id"
                  :aria-label="row.original.locked ? 'Unlock account' : 'Lock account'"
                  @click="update(row.original, { locked: !row.original.locked })"
                />
              </UTooltip>
            </div>
          </template>
        </UTable>

        <template
          v-if="collection.total.value > collection.pageSize"
          #footer
        >
          <p class="text-sm text-muted">
            Showing {{ collection.paginatedItems.value.length }} of {{ collection.total.value }} users
          </p>
          <UPagination
            v-model:page="collection.page.value"
            :total="collection.total.value"
            :items-per-page="collection.pageSize"
            size="sm"
          />
        </template>
      </IdentityTableCard>
    </template>
  </IdentityPanel>
</template>
