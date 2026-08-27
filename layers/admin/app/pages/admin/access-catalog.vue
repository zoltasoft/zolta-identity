<script setup lang="ts">
import type { TableColumn } from '@nuxt/ui'
import type {
  IdentityAccessCatalogPermission,
  IdentityAccessCatalogRole
} from '#admin/types/identity-access'

definePageMeta({ layout: 'identity-admin', middleware: ['identity-system-admin'] })

const access = useIdentityAccess()
const { data: catalog, status, error, refresh } = await access.projectAccessCatalog()
const permissions = computed(() => catalog.value?.permissions ?? [])
const roles = computed(() => catalog.value?.roles ?? [])
const permissionModalOpen = ref(false)
const roleModalOpen = ref(false)
const savingPermission = ref(false)
const savingRole = ref(false)
const permissionForm = reactive({ key: '', name: '', description: '' })
const roleForm = reactive({ name: '', slug: '', description: '', permissionIds: [] as string[] })
const permissionCollection = useIdentityCollection(permissions, permission => (
  `${permission.key} ${permission.name} ${permission.description ?? ''}`
))
const roleCollection = useIdentityCollection(roles, role => (
  `${role.name} ${role.slug} ${role.description ?? ''}`
))
const permissionOptions = computed(() => permissions.value.map(permission => ({
  label: `${permission.key} · ${permission.name}`,
  value: permission.id
})))
const permissionById = computed(() => new Map(permissions.value.map(permission => [permission.id, permission])))
const permissionColumns: TableColumn<IdentityAccessCatalogPermission>[] = [
  { accessorKey: 'key', header: 'Permission key' },
  { accessorKey: 'name', header: 'Name' },
  { accessorKey: 'status', header: 'Status' },
  { accessorKey: 'version', header: 'Version' }
]
const roleColumns: TableColumn<IdentityAccessCatalogRole>[] = [
  { accessorKey: 'name', header: 'Reusable role' },
  { accessorKey: 'permissions', header: 'Permissions' },
  { accessorKey: 'status', header: 'Status' },
  { accessorKey: 'version', header: 'Version' }
]

function rolePermissions(role: IdentityAccessCatalogRole): IdentityAccessCatalogPermission[] {
  return role.permission_ids
    .map(id => permissionById.value.get(id))
    .filter((permission): permission is IdentityAccessCatalogPermission => permission !== undefined)
}

async function createPermission() {
  savingPermission.value = true
  try {
    await access.createCatalogPermission({
      key: permissionForm.key,
      name: permissionForm.name || null,
      description: permissionForm.description || null
    })
    Object.assign(permissionForm, { key: '', name: '', description: '' })
    permissionModalOpen.value = false
    await refresh()
  } finally {
    savingPermission.value = false
  }
}

async function createRole() {
  savingRole.value = true
  try {
    await access.createCatalogRole({
      name: roleForm.name,
      slug: roleForm.slug,
      description: roleForm.description || null,
      permission_ids: roleForm.permissionIds
    })
    Object.assign(roleForm, { name: '', slug: '', description: '', permissionIds: [] })
    roleModalOpen.value = false
    await refresh()
  } finally {
    savingRole.value = false
  }
}
</script>

<template>
  <IdentityPanel
    panel-id="identity-access-catalog"
    title="Access catalog"
    icon="i-lucide-library"
    description="Create reusable roles and permissions that projects can import without coupling their authorization data."
  >
    <IdentityShellState
      v-if="status === 'pending'"
      state="loading"
      title="Loading access catalog"
    />
    <IdentityShellState
      v-else-if="error"
      state="error"
      title="Unable to load access catalog"
      :description="error.statusMessage || 'The identity API did not return the access catalog.'"
      @retry="refresh()"
    />

    <div
      v-else
      class="space-y-6"
    >
      <div class="space-y-3">
        <IdentityCollectionToolbar
          v-model="permissionCollection.search.value"
          placeholder="Search catalog permissions"
          :result-count="permissionCollection.total.value"
        >
          <template #actions>
            <UButton
              label="New catalog permission"
              icon="i-lucide-key-round"
              @click="() => { permissionModalOpen = true }"
            />
          </template>
        </IdentityCollectionToolbar>

        <IdentityTableCard
          title="Reusable permissions"
          description="Stable permission keys that can be imported into any project."
          :count="permissionCollection.total.value"
        >
          <UTable
            :data="permissionCollection.paginatedItems.value"
            :columns="permissionColumns"
            empty="No catalog permissions match your search."
          >
            <template #key-cell="{ row }">
              <code class="rounded-md bg-elevated px-2 py-1 text-xs text-highlighted">
                {{ row.original.key }}
              </code>
            </template>
            <template #name-cell="{ row }">
              <div class="max-w-md py-1">
                <p class="font-medium text-highlighted">
                  {{ row.original.name }}
                </p>
                <p class="truncate text-xs text-muted">
                  {{ row.original.description || 'No description' }}
                </p>
              </div>
            </template>
            <template #status-cell="{ row }">
              <UBadge
                :color="row.original.status === 'active' ? 'success' : 'neutral'"
                variant="soft"
                class="capitalize"
              >
                {{ row.original.status }}
              </UBadge>
            </template>
            <template #version-cell="{ row }">
              <span class="text-sm tabular-nums text-muted">v{{ row.original.version }}</span>
            </template>
          </UTable>
        </IdentityTableCard>
      </div>

      <div class="space-y-3">
        <IdentityCollectionToolbar
          v-model="roleCollection.search.value"
          placeholder="Search catalog roles"
          :result-count="roleCollection.total.value"
        >
          <template #actions>
            <UButton
              label="New catalog role"
              icon="i-lucide-badge-plus"
              @click="() => { roleModalOpen = true }"
            />
          </template>
        </IdentityCollectionToolbar>

        <IdentityTableCard
          title="Reusable roles"
          description="Role templates composed from catalog permissions."
          :count="roleCollection.total.value"
        >
          <UTable
            :data="roleCollection.paginatedItems.value"
            :columns="roleColumns"
            empty="No catalog roles match your search."
          >
            <template #name-cell="{ row }">
              <div class="py-1">
                <p class="font-medium text-highlighted">
                  {{ row.original.name }}
                </p>
                <p class="text-xs text-muted">
                  {{ row.original.slug }} · {{ row.original.description || 'No description' }}
                </p>
              </div>
            </template>
            <template #permissions-cell="{ row }">
              <div class="flex max-w-lg flex-wrap gap-1.5">
                <UBadge
                  v-for="permission in rolePermissions(row.original).slice(0, 4)"
                  :key="permission.id"
                  color="neutral"
                  variant="soft"
                >
                  {{ permission.key }}
                </UBadge>
                <UBadge
                  v-if="row.original.permission_ids.length > 4"
                  color="neutral"
                  variant="outline"
                >
                  +{{ row.original.permission_ids.length - 4 }}
                </UBadge>
                <span
                  v-if="row.original.permission_ids.length === 0"
                  class="text-sm text-muted"
                >No permissions</span>
              </div>
            </template>
            <template #status-cell="{ row }">
              <UBadge
                :color="row.original.status === 'active' ? 'success' : 'neutral'"
                variant="soft"
                class="capitalize"
              >
                {{ row.original.status }}
              </UBadge>
            </template>
            <template #version-cell="{ row }">
              <span class="text-sm tabular-nums text-muted">v{{ row.original.version }}</span>
            </template>
          </UTable>
        </IdentityTableCard>
      </div>
    </div>

    <UModal
      v-model:open="permissionModalOpen"
      title="Create catalog permission"
      description="Use a stable, namespaced key that projects can reuse."
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
              v-model="permissionForm.key"
              autofocus
              required
              placeholder="documents.read"
              class="w-full"
            />
          </UFormField>
          <UFormField label="Name">
            <UInput
              v-model="permissionForm.name"
              placeholder="Read documents"
              class="w-full"
            />
          </UFormField>
          <UFormField label="Description">
            <UTextarea
              v-model="permissionForm.description"
              class="w-full"
            />
          </UFormField>
          <div class="flex justify-end gap-2">
            <UButton
              label="Cancel"
              color="neutral"
              variant="ghost"
              @click="() => { permissionModalOpen = false }"
            />
            <UButton
              type="submit"
              label="Create permission"
              :loading="savingPermission"
            />
          </div>
        </form>
      </template>
    </UModal>

    <UModal
      v-model:open="roleModalOpen"
      title="Create catalog role"
      description="Compose a reusable role from catalog permissions."
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
              v-model="roleForm.name"
              autofocus
              required
              class="w-full"
            />
          </UFormField>
          <UFormField
            label="Slug"
            required
          >
            <UInput
              v-model="roleForm.slug"
              required
              placeholder="document-reader"
              class="w-full"
            />
          </UFormField>
          <UFormField label="Description">
            <UTextarea
              v-model="roleForm.description"
              class="w-full"
            />
          </UFormField>
          <UFormField label="Permissions">
            <div class="max-h-72 overflow-y-auto rounded-xl border border-default p-3">
              <UCheckboxGroup
                v-model="roleForm.permissionIds"
                :items="permissionOptions"
                class="space-y-2"
              />
              <p
                v-if="permissionOptions.length === 0"
                class="text-sm text-muted"
              >
                Create a catalog permission first.
              </p>
            </div>
          </UFormField>
          <div class="flex justify-end gap-2">
            <UButton
              label="Cancel"
              color="neutral"
              variant="ghost"
              @click="() => { roleModalOpen = false }"
            />
            <UButton
              type="submit"
              label="Create role"
              :loading="savingRole"
            />
          </div>
        </form>
      </template>
    </UModal>
  </IdentityPanel>
</template>
