<script setup lang="ts">
import type { TableColumn } from '@nuxt/ui'
import type { IdentityGlobalRole } from '#admin/types/identity-access'

definePageMeta({ layout: 'identity-admin', middleware: ['identity-system-admin'] })

const access = useIdentityAccess()
const { data: roles, status, error, refresh } = await access.globalRoles()
const { data: permissions } = await access.globalPermissions()
const open = ref(false)
const saving = ref(false)
const deletingId = ref<string | null>(null)
const form = reactive({ name: '', description: '', permissionIds: [] as string[] })
const collection = useIdentityCollection(roles, role => (
  `${role.name} ${role.description ?? ''} ${role.permissions.map(permission => permission.name).join(' ')}`
))
const permissionOptions = computed(() => (permissions.value ?? []).map(permission => ({
  label: permission.name,
  value: permission.id
})))
const columns: TableColumn<IdentityGlobalRole>[] = [
  { accessorKey: 'name', header: 'Role' },
  { accessorKey: 'permissions', header: 'Permissions' },
  { accessorKey: 'users', header: 'Assigned users' },
  { accessorKey: 'updated_at', header: 'Updated' },
  { id: 'actions', header: '' }
]

async function createRole() {
  saving.value = true
  try {
    await access.createGlobalRole({
      name: form.name,
      description: form.description || null,
      permission_ids: form.permissionIds
    })
    Object.assign(form, { name: '', description: '', permissionIds: [] })
    open.value = false
    await refresh()
  } finally {
    saving.value = false
  }
}

async function removeRole(id: string) {
  deletingId.value = id
  try {
    await access.deleteGlobalRole(id)
    await refresh()
  } finally {
    deletingId.value = null
  }
}
</script>

<template>
  <IdentityPanel
    panel-id="identity-global-roles"
    title="Global roles"
    icon="i-lucide-badge-check"
    description="Installation-wide compatibility roles. Prefer project roles for application-specific authorization."
  >
    <IdentityShellState
      v-if="status === 'pending'"
      state="loading"
      title="Loading global roles"
    />
    <IdentityShellState
      v-else-if="error"
      state="error"
      title="Unable to load global roles"
      :description="error.statusMessage || 'The identity API did not return the role list.'"
      @retry="refresh()"
    />

    <template v-else>
      <IdentityCollectionToolbar
        v-model="collection.search.value"
        placeholder="Search global roles"
        :result-count="collection.total.value"
      >
        <template #actions>
          <UButton
            label="New global role"
            icon="i-lucide-badge-plus"
            @click="() => { open = true }"
          />
        </template>
      </IdentityCollectionToolbar>

      <IdentityTableCard
        title="Role directory"
        description="Review permission composition and global assignments."
        :count="collection.total.value"
      >
        <UTable
          :data="collection.paginatedItems.value"
          :columns="columns"
          empty="No global roles match your search."
          class="min-w-4xl"
          :ui="{
            thead: '[&>tr]:bg-elevated/50 [&>tr]:after:content-none',
            tbody: '[&>tr]:last:[&>td]:border-b-0',
            th: 'border-b border-default',
            td: 'border-b border-default'
          }"
        >
          <template #name-cell="{ row }">
            <div class="max-w-sm py-1">
              <p class="font-medium text-highlighted">
                {{ row.original.name }}
              </p>
              <p class="truncate text-xs text-muted">
                {{ row.original.description || 'No description' }}
              </p>
            </div>
          </template>
          <template #permissions-cell="{ row }">
            <div class="flex max-w-md flex-wrap gap-1.5">
              <UBadge
                v-for="permission in row.original.permissions.slice(0, 3)"
                :key="permission.id"
                color="neutral"
                variant="soft"
              >
                {{ permission.name }}
              </UBadge>
              <UBadge
                v-if="row.original.permissions.length > 3"
                color="neutral"
                variant="outline"
              >
                +{{ row.original.permissions.length - 3 }}
              </UBadge>
              <span
                v-if="!row.original.permissions.length"
                class="text-sm text-muted"
              >No permissions</span>
            </div>
          </template>
          <template #users-cell="{ row }">
            <span class="tabular-nums">{{ row.original.users.length }}</span>
          </template>
          <template #updated_at-cell="{ row }">
            <span class="whitespace-nowrap text-sm text-muted">
              {{ formatIdentityDate(row.original.updated_at) }}
            </span>
          </template>
          <template #actions-cell="{ row }">
            <div class="flex justify-end">
              <UButton
                label="Delete"
                icon="i-lucide-trash-2"
                color="error"
                variant="ghost"
                :loading="deletingId === row.original.id"
                @click="removeRole(row.original.id)"
              />
            </div>
          </template>
        </UTable>

        <template
          v-if="collection.total.value > collection.pageSize"
          #footer
        >
          <p class="text-sm text-muted">
            Showing {{ collection.paginatedItems.value.length }} of {{ collection.total.value }} roles
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

    <UModal
      v-model:open="open"
      title="Create global role"
      description="Use project roles for normal application authorization."
    >
      <template #body>
        <form
          class="space-y-4"
          @submit.prevent="createRole"
        >
          <UFormField
            label="Name"
            required
          >
            <UInput
              v-model="form.name"
              autofocus
              class="w-full"
            />
          </UFormField>
          <UFormField label="Description">
            <UTextarea
              v-model="form.description"
              class="w-full"
            />
          </UFormField>
          <UFormField label="Permissions">
            <div class="max-h-72 overflow-y-auto rounded-xl border border-default p-3">
              <UCheckboxGroup
                v-model="form.permissionIds"
                :items="permissionOptions"
                class="space-y-2"
              />
              <p
                v-if="!permissions?.length"
                class="text-sm text-muted"
              >
                Create a global permission first.
              </p>
            </div>
          </UFormField>
          <div class="flex justify-end gap-2">
            <UButton
              label="Cancel"
              color="neutral"
              variant="ghost"
              @click="() => { open = false }"
            />
            <UButton
              type="submit"
              label="Create role"
              :loading="saving"
            />
          </div>
        </form>
      </template>
    </UModal>
  </IdentityPanel>
</template>
