<script setup lang="ts">
definePageMeta({ layout: 'identity-admin', middleware: ['identity-admin'] })

const route = useRoute()
const projectId = computed(() => String(route.params.id))
const {
  project: fetchProject,
  audit: fetchAudit,
  createClient,
  updateProjectRegistration,
  rotateClientSecret,
  setClientStatus,
  createRole,
  createPermission,
  setRolePermissions,
  setMembershipAccess,
  removeMembership,
  invite
} = useIdentityAccess()
const { data: project, status, error, refresh } = await fetchProject(projectId)
const { data: auditEvents, refresh: refreshAudit } = await fetchAudit(projectId)
const toast = useToast()
const clientName = ref('')
const role = reactive({ name: '', slug: '' })
const permissionForm = reactive({ key: '', name: '', description: '' })
const invitation = reactive({ email: '', is_admin: false })
const registration = reactive<{
  mode: 'invite_only' | 'public'
  roleId: string | null
}>({ mode: 'invite_only', roleId: null })
const revealedSecret = ref<{ clientId: string, secret: string } | null>(null)
const selectedRole = ref<{ id: string, name: string, permissionIds: string[] } | null>(null)
const selectedMembership = ref<{
  id: string
  label: string
  roleIds: string[]
  permissionIds: string[]
  isAdmin: boolean
  status: 'active' | 'suspended'
} | null>(null)
const projectRoleOptions = computed(() => (project.value?.roles ?? []).map(item => ({
  label: item.name,
  value: item.id
})))
const projectPermissionOptions = computed(() => (project.value?.permissions ?? []).map(item => ({
  label: item.key,
  value: item.id
})))

watch(project, (value) => {
  if (!value) return
  registration.mode = value.registration_mode
  registration.roleId = value.registration_role_id
}, { immediate: true })

async function addClient() {
  const client = await createClient(projectId.value, clientName.value)
  revealedSecret.value = { clientId: client.id, secret: client.client_secret ?? '' }
  clientName.value = ''
  await refresh()
}

async function rotate(clientId: string) {
  const client = await rotateClientSecret(projectId.value, clientId)
  revealedSecret.value = { clientId: client.id, secret: client.client_secret ?? '' }
  await refresh()
}

async function toggleClient(clientId: string, status: 'active' | 'disabled') {
  await setClientStatus(projectId.value, clientId, status === 'active' ? 'disabled' : 'active')
  await Promise.all([refresh(), refreshAudit()])
}

async function addRole() {
  await createRole(projectId.value, role)
  Object.assign(role, { name: '', slug: '' })
  await refresh()
}

async function addPermission() {
  await createPermission(projectId.value, {
    key: permissionForm.key,
    name: permissionForm.name || null,
    description: permissionForm.description || null
  })
  Object.assign(permissionForm, { key: '', name: '', description: '' })
  await Promise.all([refresh(), refreshAudit()])
}

async function sendInvitation() {
  const result = await invite(projectId.value, invitation)
  toast.add({ title: 'Invitation created', description: `One-time token: ${String(result.invitation_token ?? '')}`, duration: 0 })
  Object.assign(invitation, { email: '', is_admin: false })
  await refresh()
}

async function saveRegistrationPolicy() {
  await updateProjectRegistration(projectId.value, {
    registration_mode: registration.mode,
    registration_role_id: registration.mode === 'public' ? registration.roleId : null
  })
  toast.add({ title: 'Registration policy saved' })
  await Promise.all([refresh(), refreshAudit()])
}

async function saveRolePermissions() {
  if (!selectedRole.value) return
  await setRolePermissions(projectId.value, selectedRole.value.id, selectedRole.value.permissionIds)
  selectedRole.value = null
  await Promise.all([refresh(), refreshAudit()])
}

async function saveMembership() {
  if (!selectedMembership.value) return
  await setMembershipAccess(projectId.value, selectedMembership.value.id, {
    role_ids: selectedMembership.value.roleIds,
    permission_ids: selectedMembership.value.permissionIds,
    is_admin: selectedMembership.value.isAdmin,
    status: selectedMembership.value.status
  })
  selectedMembership.value = null
  await Promise.all([refresh(), refreshAudit()])
}

async function removeSelectedMembership() {
  if (!selectedMembership.value) return
  await removeMembership(projectId.value, selectedMembership.value.id)
  selectedMembership.value = null
  await Promise.all([refresh(), refreshAudit()])
}

function selectRole(item: { id: string, name: string, permission_ids: string[] }) {
  selectedRole.value = { id: item.id, name: item.name, permissionIds: [...item.permission_ids] }
}

function selectMembership(membership: NonNullable<typeof project.value>['memberships'][number]) {
  selectedMembership.value = {
    id: membership.id,
    label: membership.user.username || membership.user.email || membership.user.id,
    roleIds: [...membership.role_ids],
    permissionIds: [...membership.direct_permission_ids],
    isAdmin: membership.is_admin,
    status: membership.status
  }
}
</script>

<template>
  <IdentityPanel
    panel-id="identity-project-detail"
    :title="project?.name || 'Project'"
    :description="project ? `${project.slug} · ${project.status}` : 'Loading project access configuration.'"
  >
    <IdentityShellState
      v-if="status === 'pending'"
      state="loading"
      title="Loading project"
    />
    <IdentityShellState
      v-else-if="error || !project"
      state="error"
      title="Unable to load project"
      :description="error?.statusMessage || 'The identity service did not return this project and its access configuration.'"
      @retry="refresh()"
    />

    <div
      v-else
      class="space-y-8"
    >
      <UAlert
        v-if="revealedSecret"
        color="warning"
        variant="soft"
        title="Store this client secret now"
        :description="`${revealedSecret.clientId}: ${revealedSecret.secret}`"
        icon="i-lucide-key-round"
        close
        @update:open="revealedSecret = null"
      />

      <div class="grid gap-6 xl:grid-cols-2">
        <UPageCard
          title="Confidential clients"
          description="Create one client per BFF, API, worker, or environment."
          variant="subtle"
        >
          <form
            class="mb-5 flex gap-2"
            @submit.prevent="addClient"
          >
            <UInput
              v-model="clientName"
              required
              placeholder="Portfolio BFF"
              class="flex-1"
            />
            <UButton
              type="submit"
              label="Create"
              icon="i-lucide-plus"
            />
          </form>
          <div class="space-y-3">
            <div
              v-for="client in project.clients"
              :key="client.id"
              class="flex items-center justify-between rounded-xl border border-default p-3"
            >
              <div>
                <p class="font-medium">
                  {{ client.name }}
                </p><p class="text-xs text-muted">
                  {{ client.id }} · secret {{ client.secret_prefix }}…
                </p>
              </div>
              <div class="flex gap-2">
                <UButton
                  :label="client.status === 'active' ? 'Disable' : 'Enable'"
                  :icon="client.status === 'active' ? 'i-lucide-ban' : 'i-lucide-circle-check'"
                  color="neutral"
                  variant="ghost"
                  @click="toggleClient(client.id, client.status)"
                />
                <UButton
                  label="Rotate"
                  icon="i-lucide-refresh-cw"
                  color="neutral"
                  variant="outline"
                  @click="rotate(client.id)"
                />
              </div>
            </div>
          </div>
        </UPageCard>

        <UPageCard
          title="Invite a member"
          description="Create a one-time invitation for this project."
          variant="subtle"
        >
          <form
            class="space-y-4"
            @submit.prevent="sendInvitation"
          >
            <UFormField label="Email">
              <UInput
                v-model="invitation.email"
                required
                type="email"
                class="w-full"
              />
            </UFormField>
            <UCheckbox
              v-model="invitation.is_admin"
              label="Project administrator"
            />
            <UButton
              type="submit"
              label="Create invitation"
              icon="i-lucide-mail-plus"
            />
          </form>
        </UPageCard>

        <UPageCard
          title="Registration policy"
          description="Choose whether accounts need an invitation or may join this project directly."
          variant="subtle"
        >
          <form
            class="space-y-4"
            @submit.prevent="saveRegistrationPolicy"
          >
            <UFormField label="Registration">
              <USelect
                v-model="registration.mode"
                :items="[
                  { label: 'Invitation only', value: 'invite_only' },
                  { label: 'Public registration', value: 'public' }
                ]"
                class="w-full"
              />
            </UFormField>
            <UFormField
              v-if="registration.mode === 'public'"
              label="Default role"
              description="Optional role assigned to each new project member."
            >
              <USelect
                v-model="registration.roleId"
                :items="[
                  { label: 'No default role', value: null },
                  ...project.roles.map(item => ({ label: item.name, value: item.id }))
                ]"
                class="w-full"
              />
            </UFormField>
            <UButton
              type="submit"
              label="Save policy"
              icon="i-lucide-save"
            />
          </form>
        </UPageCard>

        <UPageCard
          title="Project roles"
          description="Roles collect stable permission keys declared by consuming services."
          variant="subtle"
        >
          <form
            class="mb-5 grid gap-2 sm:grid-cols-[1fr_1fr_auto]"
            @submit.prevent="addRole"
          >
            <UInput
              v-model="role.name"
              required
              placeholder="Editor"
            />
            <UInput
              v-model="role.slug"
              required
              placeholder="editor"
            />
            <UButton
              type="submit"
              label="Add"
              icon="i-lucide-plus"
            />
          </form>
          <div class="flex flex-wrap gap-2">
            <UButton
              v-for="item in project.roles"
              :key="item.id"
              color="neutral"
              variant="outline"
              icon="i-lucide-shield"
              @click="selectRole(item)"
            >
              {{ item.name }}
            </UButton>
          </div>
        </UPageCard>

        <UPageCard
          title="Permission catalog"
          description="Manifest permissions remain visible when stale and are never silently deleted."
          variant="subtle"
        >
          <form
            class="mb-5 grid gap-2 sm:grid-cols-[1fr_1fr_auto]"
            @submit.prevent="addPermission"
          >
            <UInput
              v-model="permissionForm.key"
              required
              placeholder="documents.read"
            />
            <UInput
              v-model="permissionForm.name"
              placeholder="Read documents"
            />
            <UButton
              type="submit"
              label="Add"
              icon="i-lucide-plus"
            />
          </form>
          <div class="space-y-2">
            <div
              v-for="permission in project.permissions"
              :key="permission.id"
              class="flex items-center justify-between rounded-xl border border-default p-3"
            >
              <div>
                <p class="font-mono text-sm">
                  {{ permission.key }}
                </p><p class="text-xs text-muted">
                  {{ permission.description || permission.name }}
                </p>
              </div>
              <UBadge
                :color="permission.status === 'active' ? 'success' : 'warning'"
                variant="soft"
              >
                {{ permission.status }}
              </UBadge>
            </div>
          </div>
        </UPageCard>
      </div>

      <UPageCard
        title="Memberships"
        description="Effective access is the union of project roles and direct grants."
        variant="subtle"
      >
        <div class="divide-y divide-default">
          <div
            v-for="membership in project.memberships"
            :key="membership.id"
            class="flex flex-col gap-2 py-4 sm:flex-row sm:items-center sm:justify-between"
          >
            <div>
              <p class="font-medium">
                {{ membership.user.username || membership.user.email || membership.user.id }}
              </p><p class="text-xs text-muted">
                {{ membership.user.email }} · authorization v{{ membership.authorization_version }}
              </p>
            </div>
            <div class="flex flex-wrap gap-2">
              <UBadge
                v-if="membership.is_admin"
                color="primary"
                variant="soft"
              >
                Project admin
              </UBadge><UBadge
                v-for="item in membership.roles"
                :key="item"
                variant="outline"
              >
                {{ item }}
              </UBadge>
              <UButton
                label="Manage"
                size="xs"
                color="neutral"
                variant="outline"
                icon="i-lucide-settings-2"
                @click="selectMembership(membership)"
              />
            </div>
          </div>
        </div>
      </UPageCard>

      <UModal
        :open="selectedRole != null"
        title="Role permissions"
        :description="selectedRole ? `Configure ${selectedRole.name}` : ''"
        @update:open="value => { if (!value) selectedRole = null }"
      >
        <template #body>
          <form
            v-if="selectedRole"
            class="space-y-5"
            @submit.prevent="saveRolePermissions"
          >
            <UCheckboxGroup
              v-model="selectedRole.permissionIds"
              :items="projectPermissionOptions"
              class="grid gap-3 sm:grid-cols-2"
            />
            <div class="flex justify-end">
              <UButton
                type="submit"
                label="Save role"
                icon="i-lucide-save"
              />
            </div>
          </form>
        </template>
      </UModal>

      <UModal
        :open="selectedMembership != null"
        title="Membership access"
        :description="selectedMembership?.label"
        @update:open="value => { if (!value) selectedMembership = null }"
      >
        <template #body>
          <form
            v-if="selectedMembership"
            class="space-y-6"
            @submit.prevent="saveMembership"
          >
            <UCheckbox
              v-model="selectedMembership.isAdmin"
              label="Project administrator"
            />
            <UFormField label="Status">
              <USelect
                v-model="selectedMembership.status"
                :items="[{ label: 'Active', value: 'active' }, { label: 'Suspended', value: 'suspended' }]"
                class="w-full"
              />
            </UFormField>
            <div>
              <p class="mb-3 text-sm font-medium">
                Roles
              </p>
              <UCheckboxGroup
                v-model="selectedMembership.roleIds"
                :items="projectRoleOptions"
                class="grid gap-3 sm:grid-cols-2"
              />
            </div>
            <div>
              <p class="mb-3 text-sm font-medium">
                Direct permission grants
              </p>
              <UCheckboxGroup
                v-model="selectedMembership.permissionIds"
                :items="projectPermissionOptions"
                class="grid gap-3 sm:grid-cols-2"
              />
            </div>
            <div class="flex justify-between gap-3">
              <UButton
                type="button"
                label="Remove membership"
                color="error"
                variant="outline"
                icon="i-lucide-user-minus"
                @click="removeSelectedMembership"
              />
              <UButton
                type="submit"
                label="Save access"
                icon="i-lucide-save"
              />
            </div>
          </form>
        </template>
      </UModal>

      <UPageCard
        title="Audit history"
        description="Authentication and access-management changes for this project."
        variant="subtle"
      >
        <div class="divide-y divide-default">
          <div
            v-for="item in auditEvents"
            :key="item.id"
            class="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between"
          >
            <div>
              <p class="font-mono text-sm">
                {{ item.event }}
              </p>
              <p class="text-xs text-muted">
                {{ item.target_type || 'project' }} {{ item.target_id || projectId }}
              </p>
            </div>
            <time class="text-xs text-muted">{{ item.created_at }}</time>
          </div>
          <p
            v-if="!auditEvents?.length"
            class="py-4 text-sm text-muted"
          >
            No audit events recorded yet.
          </p>
        </div>
      </UPageCard>
    </div>
  </IdentityPanel>
</template>
