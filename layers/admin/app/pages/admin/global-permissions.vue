<script setup lang="ts">
import type { TableColumn } from '@nuxt/ui'
import type { IdentityGlobalPermission } from '#admin/types/identity-access'

definePageMeta({ layout: 'identity-admin', middleware: ['identity-system-admin'] })

const access = useIdentityAccess()
const { data: permissions, status, error, refresh } = await access.globalPermissions()
const open = ref(false)
const saving = ref(false)
const deletingId = ref<string | null>(null)
const form = reactive({ name: '', description: '' })
const collection = useIdentityCollection(permissions, permission => (
  `${permission.name} ${permission.description ?? ''}`
))
const columns: TableColumn<IdentityGlobalPermission>[] = [
  { accessorKey: 'name', header: 'Permission key' },
  { accessorKey: 'description', header: 'Description' },
  { accessorKey: 'roles', header: 'Roles' },
  { accessorKey: 'users', header: 'Direct users' },
  { accessorKey: 'updated_at', header: 'Updated' },
  { id: 'actions', header: '' }
]

async function createPermission() {
  saving.value = true
  try {
    await access.createGlobalPermission({ name: form.name, description: form.description || null })
    Object.assign(form, { name: '', description: '' })
    open.value = false
    await refresh()
  } finally {
    saving.value = false
  }
}

async function removePermission(id: string) {
  deletingId.value = id
  try {
    await access.deleteGlobalPermission(id)
    await refresh()
  } finally {
    deletingId.value = null
  }
}
</script>

<template>
  <IdentityPanel
    panel-id="identity-global-permissions"
    title="Global permissions"
    icon="i-lucide-key-round"
    description="Installation-wide grants retained for migrated clients. New applications should publish project permission manifests."
  >
    <IdentityShellState
      v-if="status === 'pending'"
      state="loading"
      title="Loading global permissions"
    />
    <IdentityShellState
      v-else-if="error"
      state="error"
      title="Unable to load global permissions"
      :description="error.statusMessage || 'The identity API did not return the permission list.'"
      @retry="refresh()"
    />

    <template v-else>
      <IdentityCollectionToolbar
        v-model="collection.search.value"
        placeholder="Search permission keys"
        :result-count="collection.total.value"
      >
        <template #actions>
          <UButton
            label="New permission"
            icon="i-lucide-key-round"
            @click="() => { open = true }"
          />
        </template>
      </IdentityCollectionToolbar>

      <IdentityTableCard
        title="Permission catalog"
        description="Namespaced keys and their installation-level usage."
        :count="collection.total.value"
      >
        <UTable
          :data="collection.paginatedItems.value"
          :columns="columns"
          empty="No global permissions match your search."
          class="min-w-5xl"
          :ui="{
            thead: '[&>tr]:bg-elevated/50 [&>tr]:after:content-none',
            tbody: '[&>tr]:last:[&>td]:border-b-0',
            th: 'border-b border-default',
            td: 'border-b border-default'
          }"
        >
          <template #name-cell="{ row }">
            <code class="rounded-md bg-elevated px-2 py-1 text-xs text-highlighted">
              {{ row.original.name }}
            </code>
          </template>
          <template #description-cell="{ row }">
            <p class="max-w-md truncate text-sm text-muted">
              {{ row.original.description || 'No description' }}
            </p>
          </template>
          <template #roles-cell="{ row }">
            <span class="tabular-nums">{{ row.original.roles.length }}</span>
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
                @click="removePermission(row.original.id)"
              />
            </div>
          </template>
        </UTable>

        <template
          v-if="collection.total.value > collection.pageSize"
          #footer
        >
          <p class="text-sm text-muted">
            Showing {{ collection.paginatedItems.value.length }} of {{ collection.total.value }} permissions
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
      title="Create global permission"
      description="Use a stable, namespaced permission key."
    >
      <template #body>
        <form
          class="space-y-4"
          @submit.prevent="createPermission"
        >
          <UFormField
            label="Permission key"
            required
          >
            <UInput
              v-model="form.name"
              autofocus
              placeholder="identity.users.audit"
              class="w-full"
            />
          </UFormField>
          <UFormField label="Description">
            <UTextarea
              v-model="form.description"
              class="w-full"
            />
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
              label="Create permission"
              :loading="saving"
            />
          </div>
        </form>
      </template>
    </UModal>
  </IdentityPanel>
</template>
