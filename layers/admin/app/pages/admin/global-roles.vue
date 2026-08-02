<script setup lang="ts">
definePageMeta({ layout: 'identity-admin', middleware: ['identity-system-admin'] })

const access = useIdentityAccess()
const { data: roles, status, error, refresh } = await access.globalRoles()
const { data: permissions } = await access.globalPermissions()
const open = ref(false)
const saving = ref(false)
const deletingId = ref<string | null>(null)
const form = reactive({ name: '', description: '', permissionIds: [] as string[] })
const permissionOptions = computed(() => (permissions.value ?? []).map(permission => ({
  label: permission.name,
  value: permission.id
})))

function openForm() {
  open.value = true
}

function closeForm() {
  open.value = false
}

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
    description="Installation-wide roles support the compatibility API. Application-specific access remains isolated inside each project."
  >
    <template #actions>
      <UButton
        label="New global role"
        icon="i-lucide-badge-plus"
        @click="openForm"
      />
    </template>

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
    <IdentityShellState
      v-else-if="!roles?.length"
      state="empty"
      title="No global roles"
      description="Create an installation-wide role or manage project roles from a project page."
    />
    <div
      v-else
      class="grid gap-4 lg:grid-cols-2"
    >
      <UPageCard
        v-for="role in roles"
        :key="role.id"
        :title="role.name"
        :description="role.description || 'No description'"
        variant="subtle"
      >
        <div class="flex flex-wrap gap-2">
          <UBadge
            v-for="permission in role.permissions"
            :key="permission.id"
            color="neutral"
            variant="soft"
          >
            {{ permission.name }}
          </UBadge>
          <span
            v-if="!role.permissions.length"
            class="text-sm text-muted"
          >No permissions</span>
        </div>
        <div class="mt-4 flex items-center justify-between text-sm text-muted">
          <span>{{ role.users.length }} assigned user{{ role.users.length === 1 ? '' : 's' }}</span>
          <UButton
            label="Delete"
            icon="i-lucide-trash-2"
            color="error"
            variant="ghost"
            :loading="deletingId === role.id"
            @click="removeRole(role.id)"
          />
        </div>
      </UPageCard>
    </div>

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
            <div class="rounded-xl border border-default p-3">
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
              @click="closeForm"
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
