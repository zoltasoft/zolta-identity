<script setup lang="ts">
definePageMeta({ layout: 'identity-admin', middleware: ['identity-system-admin'] })

const access = useIdentityAccess()
const { data: permissions, status, error, refresh } = await access.globalPermissions()
const open = ref(false)
const saving = ref(false)
const deletingId = ref<string | null>(null)
const form = reactive({ name: '', description: '' })

function openForm() {
  open.value = true
}

function closeForm() {
  open.value = false
}

async function createPermission() {
  saving.value = true
  try {
    await access.createGlobalPermission({
      name: form.name,
      description: form.description || null
    })
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
    description="Installation-wide permissions are retained for migrated clients. New applications should publish project permission manifests."
  >
    <template #actions>
      <UButton
        label="New global permission"
        icon="i-lucide-key-round"
        @click="openForm"
      />
    </template>

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
    <IdentityShellState
      v-else-if="!permissions?.length"
      state="empty"
      title="No global permissions"
      description="Create an installation-wide permission or publish a project manifest from a confidential client."
    />
    <div
      v-else
      class="divide-y divide-default rounded-2xl border border-default px-5"
    >
      <div
        v-for="permission in permissions"
        :key="permission.id"
        class="flex flex-col gap-3 py-5 sm:flex-row sm:items-center sm:justify-between"
      >
        <div>
          <p class="font-medium">
            {{ permission.name }}
          </p>
          <p class="text-sm text-muted">
            {{ permission.description || 'No description' }}
          </p>
          <p class="mt-1 text-xs text-muted">
            {{ permission.roles.length }} roles · {{ permission.users.length }} direct users
          </p>
        </div>
        <UButton
          label="Delete"
          icon="i-lucide-trash-2"
          color="error"
          variant="ghost"
          :loading="deletingId === permission.id"
          @click="removePermission(permission.id)"
        />
      </div>
    </div>

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
              @click="closeForm"
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
