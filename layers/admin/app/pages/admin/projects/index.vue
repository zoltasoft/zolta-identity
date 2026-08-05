<script setup lang="ts">
import type { TableColumn } from '@nuxt/ui'
import type { IdentityProject } from '#admin/types/identity-access'

definePageMeta({ layout: 'identity-admin', middleware: ['identity-admin'] })

const localePath = useLocalePath()
const { projects: fetchProjects, createProject } = useIdentityAccess()
const { data: projects, status, error, refresh } = await fetchProjects()
const open = ref(false)
const saving = ref(false)
const mode = ref<'all' | 'live' | 'sandbox'>('all')
const form = reactive({ name: '', slug: '', description: '' })

const scopedProjects = computed(() => (projects.value ?? []).filter(project => (
  mode.value === 'all' || project.mode === mode.value
)))
const collection = useIdentityCollection(scopedProjects, project => (
  `${project.name} ${project.slug} ${project.description ?? ''} ${project.status} ${project.mode}`
))

const columns: TableColumn<IdentityProject>[] = [
  { accessorKey: 'name', header: 'Project' },
  { accessorKey: 'mode', header: 'Environment' },
  { accessorKey: 'registration_mode', header: 'Registration' },
  { accessorKey: 'status', header: 'Status' },
  { id: 'actions', header: '' }
]

watch(() => form.name, (name) => {
  if (!form.slug) form.slug = name.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
})

watch(mode, () => {
  collection.page.value = 1
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
</script>

<template>
  <IdentityPanel
    panel-id="identity-projects"
    title="Projects"
    icon="i-lucide-layout-dashboard"
    description="Manage the applications, environments, and access boundaries that trust this identity installation."
  >
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

    <template v-else>
      <IdentityCollectionToolbar
        v-model="collection.search.value"
        placeholder="Search projects"
        :result-count="collection.total.value"
      >
        <template #filters>
          <USelect
            v-model="mode"
            :items="[
              { label: 'All environments', value: 'all' },
              { label: 'Live', value: 'live' },
              { label: 'Sandbox', value: 'sandbox' }
            ]"
            size="lg"
            class="w-full sm:w-48"
          />
        </template>

        <template #actions>
          <UButton
            label="New project"
            icon="i-lucide-folder-plus"
            @click="() => { open = true }"
          />
        </template>
      </IdentityCollectionToolbar>

      <IdentityTableCard
        title="Project directory"
        description="Open a project to manage members, clients, permissions, webhooks, and audit history."
        :count="collection.total.value"
      >
        <UTable
          :data="collection.paginatedItems.value"
          :columns="columns"
          empty="No projects match the current filters."
          class="min-w-4xl"
          :ui="{
            thead: '[&>tr]:bg-elevated/50 [&>tr]:after:content-none',
            tbody: '[&>tr]:last:[&>td]:border-b-0',
            th: 'border-b border-default',
            td: 'border-b border-default'
          }"
        >
          <template #name-cell="{ row }">
            <div class="min-w-0 py-1">
              <NuxtLink
                :to="localePath(`/admin/projects/${row.original.id}`)"
                class="font-medium text-highlighted hover:text-primary"
              >
                {{ row.original.name }}
              </NuxtLink>
              <p class="max-w-sm truncate text-xs text-muted">
                {{ row.original.description || row.original.slug }}
              </p>
            </div>
          </template>
          <template #mode-cell="{ row }">
            <UBadge
              :color="row.original.mode === 'live' ? 'success' : 'warning'"
              variant="soft"
              class="capitalize"
            >
              {{ row.original.mode }}
            </UBadge>
          </template>
          <template #registration_mode-cell="{ row }">
            <span class="text-sm text-muted">
              {{ row.original.registration_mode === 'public' ? 'Public' : 'Invite only' }}
            </span>
          </template>
          <template #status-cell="{ row }">
            <div class="flex items-center gap-2 text-sm capitalize">
              <span
                class="size-2 rounded-full"
                :class="row.original.status === 'active' ? 'bg-success' : 'bg-warning'"
              />
              {{ row.original.status }}
            </div>
          </template>
          <template #actions-cell="{ row }">
            <div class="flex justify-end">
              <UButton
                label="Manage"
                icon="i-lucide-arrow-up-right"
                color="neutral"
                variant="ghost"
                :to="localePath(`/admin/projects/${row.original.id}`)"
              />
            </div>
          </template>
        </UTable>

        <template
          v-if="collection.total.value > collection.pageSize"
          #footer
        >
          <p class="text-sm text-muted">
            Page {{ collection.page.value }} of {{ Math.ceil(collection.total.value / collection.pageSize) }}
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
              autofocus
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
              @click="() => { open = false }"
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
