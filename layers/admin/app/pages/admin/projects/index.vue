<script setup lang="ts">
definePageMeta({ layout: 'identity-admin', middleware: ['identity-admin'] })

const localePath = useLocalePath()
const { projects: fetchProjects, createProject } = useIdentityAccess()
const { data: projects, status, error, refresh } = await fetchProjects()
const open = ref(false)
const saving = ref(false)
const form = reactive({ name: '', slug: '', description: '' })

watch(() => form.name, (name) => {
  if (!form.slug) form.slug = name.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
})

async function submit() {
  saving.value = true
  try {
    await createProject({ ...form, description: form.description || null })
    Object.assign(form, { name: '', slug: '', description: '' })
    open.value = false
    await refresh()
  } finally {
    saving.value = false
  }
}

function openProjectForm() {
  open.value = true
}

function closeProjectForm() {
  open.value = false
}
</script>

<template>
  <IdentityPanel
    panel-id="identity-projects"
    title="Projects"
    description="Manage the applications that trust this identity installation."
  >
    <template #actions>
      <UButton
        label="New project"
        icon="i-lucide-folder-plus"
        @click="openProjectForm"
      />
    </template>

    <IdentityShellState
      v-if="status === 'pending'"
      state="loading"
      title="Loading projects"
    />
    <IdentityShellState
      v-else-if="error"
      state="error"
      title="Unable to load projects"
      :description="error.statusMessage || 'The identity service did not return the project list.'"
      @retry="refresh()"
    />
    <IdentityShellState
      v-else-if="!projects?.length"
      state="empty"
      title="No projects yet"
      description="Create the first project to issue client credentials and manage access."
    />

    <div
      v-else
      class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
    >
      <UPageCard
        v-for="project in projects"
        :key="project.id"
        :title="project.name"
        :description="project.description || `Project slug: ${project.slug}`"
        :to="localePath(`/admin/projects/${project.id}`)"
        variant="subtle"
      >
        <div class="flex items-center justify-between">
          <UBadge
            :color="project.status === 'active' ? 'success' : 'warning'"
            variant="soft"
          >
            {{ project.status }}
          </UBadge>
          <UIcon
            name="i-lucide-arrow-up-right"
            class="size-4 text-muted"
          />
        </div>
      </UPageCard>
    </div>

    <UModal
      v-model:open="open"
      title="Create project"
      description="A project isolates its clients, members, roles, and permissions."
    >
      <template #body>
        <form
          class="space-y-4"
          @submit.prevent="submit"
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
          <UFormField
            label="Slug"
            required
          >
            <UInput
              v-model="form.slug"
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
              @click="closeProjectForm"
            />
            <UButton
              type="submit"
              label="Create project"
              icon="i-lucide-folder-plus"
              :loading="saving"
            />
          </div>
        </form>
      </template>
    </UModal>
  </IdentityPanel>
</template>
