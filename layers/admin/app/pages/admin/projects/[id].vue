<script setup lang="ts">
import type { TableColumn } from '@nuxt/ui'
import type {
  IdentityAuditEvent,
  IdentityClient,
  IdentityHostedApplication,
  IdentityMembership,
  IdentityPermission,
  IdentityRole,
  IdentityWebhook
} from '#admin/types/identity-access'

definePageMeta({ layout: 'identity-admin', middleware: ['identity-admin'] })

type ProjectTab = 'overview' | 'members' | 'access' | 'clients' | 'hosted-applications' | 'webhooks' | 'audit'

const route = useRoute()
const router = useRouter()
const localePath = useLocalePath()
const projectId = computed(() => String(route.params.id))
const access = useIdentityAccess()
const { data: project, status, error, refresh } = await access.project(projectId)
const auditRequest = await access.audit(projectId, { immediate: false })
const toast = useToast()
const routeTab = Array.isArray(route.query.tab) ? route.query.tab[0] : route.query.tab
const validTabs: ProjectTab[] = ['overview', 'members', 'access', 'clients', 'hosted-applications', 'webhooks', 'audit']
const activeTab = ref<ProjectTab>(validTabs.includes(routeTab as ProjectTab) ? routeTab as ProjectTab : 'overview')
const auditLoaded = ref(false)
const auditVisibleCount = ref(20)
const auditSentinel = ref<HTMLElement | null>(null)
const clientModalOpen = ref(false)
const hostedApplicationModalOpen = ref(false)
const invitationModalOpen = ref(false)
const roleModalOpen = ref(false)
const permissionModalOpen = ref(false)
const webhookModalOpen = ref(false)
const clientName = ref('')
const hostedApplicationBackgroundOptions = [
  { label: 'Identity default', value: 'identity' },
  { label: 'Slate', value: 'slate' },
  { label: 'Indigo', value: 'indigo' },
  { label: 'Emerald', value: 'emerald' },
  { label: 'Sunset', value: 'sunset' }
]
const hostedApplication = reactive<{
  id: string | null
  name: string
  key: string
  primaryClientId: string
  sandboxClientId: string
  applicationUrl: string
  callbackUrl: string
  welcomeText: string
  accentColor: string
  backgroundPreset: 'identity' | 'slate' | 'indigo' | 'emerald' | 'sunset'
  logoUrl: string | null
  googleEnabled: boolean
  termsRequired: boolean
  termsUrl: string
  privacyUrl: string
  status: 'active' | 'disabled'
}>({ id: null, name: '', key: '', primaryClientId: '', sandboxClientId: '', applicationUrl: '', callbackUrl: '', welcomeText: '', accentColor: '', backgroundPreset: 'identity', logoUrl: null, googleEnabled: false, termsRequired: false, termsUrl: '', privacyUrl: '', status: 'active' })
const hostedApplicationLogo = ref<File | null>(null)
const hostedApplicationLogoPreview = ref<string | null>(null)
const removeHostedApplicationLogo = ref(false)
const role = reactive({ name: '', slug: '', description: '' })
const permissionForm = reactive({ key: '', name: '', description: '' })
const invitation = reactive({ email: '', is_admin: false })
const registration = reactive<{ mode: 'invite_only' | 'public', roleId: string | null, emailVerificationRequired: boolean }>({ mode: 'invite_only', roleId: null, emailVerificationRequired: true })
const environment = reactive<{ mode: 'live' | 'sandbox', ttlMinutes: number }>({ mode: 'live', ttlMinutes: 60 })
const webhookForm = reactive({
  url: '',
  events: ['identity.user.expired'] as Array<'identity.user.expired' | 'identity.user.deletion_requested'>
})
const revealedWebhookSecret = ref<{ id: string, secret: string } | null>(null)
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

const tabs = computed(() => [
  { value: 'overview', label: 'Overview', icon: 'i-lucide-layout-dashboard' },
  { value: 'members', label: 'Members', icon: 'i-lucide-users', badge: project.value?.memberships.length ?? 0 },
  { value: 'access', label: 'Roles & permissions', icon: 'i-lucide-shield-check' },
  { value: 'clients', label: 'Clients', icon: 'i-lucide-server-cog', badge: project.value?.clients.length ?? 0 },
  { value: 'hosted-applications', label: 'Hosted apps', icon: 'i-lucide-panel-top', badge: project.value?.hosted_applications.length ?? 0 },
  { value: 'webhooks', label: 'Webhooks', icon: 'i-lucide-webhook', badge: project.value?.webhooks.length ?? 0 },
  { value: 'audit', label: 'Audit', icon: 'i-lucide-scroll-text' }
])
const projectRoleOptions = computed(() => (project.value?.roles ?? []).map(item => ({ label: item.name, value: item.id })))
const projectPermissionOptions = computed(() => (project.value?.permissions ?? []).map(item => ({ label: item.key, value: item.id })))
const primaryClientOptions = computed(() => (project.value?.clients ?? [])
  .filter(item => item.status === 'active')
  .map(item => ({ label: `${item.name} (${item.secret_prefix})`, value: item.id })))
const auditEvents = computed(() => auditRequest.data.value ?? [])
const visibleAuditEvents = computed(() => auditEvents.value.slice(0, auditVisibleCount.value))
const hasMoreAuditEvents = computed(() => auditVisibleCount.value < auditEvents.value.length)
const hostedApplicationPreviewStyle = computed(() => ({
  'background': {
    identity: 'linear-gradient(135deg, #f6f7fb, #e7ebf5)',
    slate: 'linear-gradient(135deg, #e9eff7, #cad7e8)',
    indigo: 'linear-gradient(135deg, #eef0ff, #d5dcff)',
    emerald: 'linear-gradient(135deg, #e9f8f2, #c7ecdc)',
    sunset: 'linear-gradient(135deg, #fff2ea, #ffd9cd)'
  }[hostedApplication.backgroundPreset],
  '--hosted-app-accent': hostedApplication.accentColor || '#3157d5'
}))
const hostedApplicationPreviewLogo = computed(() => {
  if (removeHostedApplicationLogo.value) return null
  return hostedApplicationLogoPreview.value || hostedApplication.logoUrl
})

const memberColumns: TableColumn<IdentityMembership>[] = [
  { accessorKey: 'user', header: 'Member' },
  { accessorKey: 'roles', header: 'Roles' },
  { accessorKey: 'permissions', header: 'Effective permissions' },
  { accessorKey: 'status', header: 'Status' },
  { id: 'actions', header: '' }
]
const roleColumns: TableColumn<IdentityRole>[] = [
  { accessorKey: 'name', header: 'Role' },
  { accessorKey: 'permission_ids', header: 'Permissions' },
  { id: 'actions', header: '' }
]
const permissionColumns: TableColumn<IdentityPermission>[] = [
  { accessorKey: 'key', header: 'Permission key' },
  { accessorKey: 'name', header: 'Name' },
  { accessorKey: 'source', header: 'Source' },
  { accessorKey: 'status', header: 'Status' }
]
const clientColumns: TableColumn<IdentityClient>[] = [
  { accessorKey: 'name', header: 'Client' },
  { accessorKey: 'secret_prefix', header: 'Credential' },
  { accessorKey: 'last_used_at', header: 'Last used' },
  { accessorKey: 'status', header: 'Status' },
  { id: 'actions', header: '' }
]
const webhookColumns: TableColumn<IdentityWebhook>[] = [
  { accessorKey: 'url', header: 'Endpoint' },
  { accessorKey: 'events', header: 'Events' },
  { accessorKey: 'last_delivered_at', header: 'Last delivery' },
  { accessorKey: 'status', header: 'Status' },
  { id: 'actions', header: '' }
]
const auditColumns: TableColumn<IdentityAuditEvent>[] = [
  { accessorKey: 'event', header: 'Event' },
  { accessorKey: 'target_type', header: 'Target' },
  { accessorKey: 'actor_user_id', header: 'Actor' },
  { accessorKey: 'ip_address', header: 'IP address' },
  { accessorKey: 'created_at', header: 'Date' }
]

watch(project, (value) => {
  if (!value) return
  registration.mode = value.registration_mode
  registration.roleId = value.registration_role_id
  registration.emailVerificationRequired = value.email_verification_required
  environment.mode = value.mode
  environment.ttlMinutes = value.sandbox_ttl_minutes
}, { immediate: true })

watch(activeTab, async (tab) => {
  if (route.query.tab !== tab) {
    await router.replace({ query: { ...route.query, tab } })
  }
  if (tab === 'audit' && !auditLoaded.value) {
    await auditRequest.execute()
    auditLoaded.value = true
  }
}, { immediate: true })

useIntersectionObserver(auditSentinel, ([entry]) => {
  if (entry?.isIntersecting && activeTab.value === 'audit' && hasMoreAuditEvents.value) {
    auditVisibleCount.value += 20
  }
})

async function refreshProjectAndAudit() {
  await refresh()
  if (auditLoaded.value) await auditRequest.refresh()
}

async function addClient() {
  const client = await access.createClient(projectId.value, clientName.value)
  revealedSecret.value = { clientId: client.id, secret: client.client_secret ?? '' }
  clientName.value = ''
  clientModalOpen.value = false
  await refreshProjectAndAudit()
}

async function rotateClient(clientId: string) {
  const client = await access.rotateClientSecret(projectId.value, clientId)
  revealedSecret.value = { clientId: client.id, secret: client.client_secret ?? '' }
  await refreshProjectAndAudit()
}

function openHostedApplication(application?: IdentityHostedApplication) {
  Object.assign(hostedApplication, application
    ? {
        id: application.id,
        name: application.name,
        key: application.key,
        primaryClientId: application.primary_client_id,
        sandboxClientId: application.sandbox_client_id ?? '',
        applicationUrl: application.application_url,
        callbackUrl: application.callback_url,
        welcomeText: application.appearance.welcome_text ?? '',
        accentColor: application.appearance.accent_color ?? '',
        backgroundPreset: application.appearance.background_preset,
        logoUrl: application.appearance.logo_url,
        googleEnabled: application.authentication.google_enabled,
        termsRequired: application.authentication.terms_required,
        termsUrl: application.authentication.terms_url ?? '',
        privacyUrl: application.authentication.privacy_url ?? '',
        status: application.status
      }
    : { id: null, name: '', key: '', primaryClientId: '', sandboxClientId: '', applicationUrl: '', callbackUrl: '', welcomeText: '', accentColor: '', backgroundPreset: 'identity', logoUrl: null, googleEnabled: false, termsRequired: false, termsUrl: '', privacyUrl: '', status: 'active' })
  hostedApplicationLogo.value = null
  hostedApplicationLogoPreview.value = null
  removeHostedApplicationLogo.value = false
  hostedApplicationModalOpen.value = true
}

function hostedApplicationAppearance() {
  return {
    welcome_text: hostedApplication.welcomeText.trim() || null,
    accent_color: hostedApplication.accentColor || null,
    background_preset: hostedApplication.backgroundPreset,
    logo_url: hostedApplication.logoUrl
  }
}

function hostedApplicationAuthentication() {
  return {
    google_enabled: hostedApplication.googleEnabled,
    terms_required: hostedApplication.termsRequired,
    terms_url: hostedApplication.termsRequired ? hostedApplication.termsUrl.trim() || null : null,
    privacy_url: hostedApplication.privacyUrl.trim() || null
  }
}

function chooseHostedApplicationLogo(event: Event) {
  const input = event.target as HTMLInputElement
  const logo = input.files?.[0] ?? null
  if (hostedApplicationLogoPreview.value) URL.revokeObjectURL(hostedApplicationLogoPreview.value)
  hostedApplicationLogo.value = logo
  hostedApplicationLogoPreview.value = logo ? URL.createObjectURL(logo) : null
  removeHostedApplicationLogo.value = false
}

async function saveHostedApplication() {
  let applicationId = hostedApplication.id
  if (hostedApplication.id) {
    await access.updateHostedApplication(projectId.value, hostedApplication.id, {
      name: hostedApplication.name,
      primary_client_id: hostedApplication.primaryClientId,
      sandbox_client_id: hostedApplication.sandboxClientId || null,
      application_url: hostedApplication.applicationUrl,
      callback_url: hostedApplication.callbackUrl,
      status: hostedApplication.status,
      appearance: hostedApplicationAppearance(),
      authentication: hostedApplicationAuthentication()
    })
  } else {
    const created = await access.createHostedApplication(projectId.value, {
      name: hostedApplication.name,
      key: hostedApplication.key,
      primary_client_id: hostedApplication.primaryClientId,
      sandbox_client_id: hostedApplication.sandboxClientId || null,
      application_url: hostedApplication.applicationUrl,
      callback_url: hostedApplication.callbackUrl,
      appearance: hostedApplicationAppearance(),
      authentication: hostedApplicationAuthentication()
    })
    applicationId = created.id
  }

  if (applicationId && hostedApplicationLogo.value) {
    await access.uploadHostedApplicationLogo(projectId.value, applicationId, hostedApplicationLogo.value)
  } else if (applicationId && removeHostedApplicationLogo.value) {
    await access.removeHostedApplicationLogo(projectId.value, applicationId)
  }
  hostedApplicationModalOpen.value = false
  await refreshProjectAndAudit()
}

async function deleteHostedApplication(applicationId: string) {
  await access.removeHostedApplication(projectId.value, applicationId)
  await refreshProjectAndAudit()
}

async function toggleClient(client: IdentityClient) {
  await access.setClientStatus(projectId.value, client.id, client.status === 'active' ? 'disabled' : 'active')
  await refreshProjectAndAudit()
}

async function addRole() {
  await access.createRole(projectId.value, { ...role, description: role.description || null })
  Object.assign(role, { name: '', slug: '', description: '' })
  roleModalOpen.value = false
  await refreshProjectAndAudit()
}

async function addPermission() {
  await access.createPermission(projectId.value, {
    key: permissionForm.key,
    name: permissionForm.name || null,
    description: permissionForm.description || null
  })
  Object.assign(permissionForm, { key: '', name: '', description: '' })
  permissionModalOpen.value = false
  await refreshProjectAndAudit()
}

async function sendInvitation() {
  const result = await access.invite(projectId.value, invitation)
  toast.add({ title: 'Invitation created', description: `One-time token: ${String(result.invitation_token ?? '')}`, duration: 0 })
  Object.assign(invitation, { email: '', is_admin: false })
  invitationModalOpen.value = false
  await refreshProjectAndAudit()
}

async function saveRegistrationPolicy() {
  await access.updateProjectRegistration(projectId.value, {
    registration_mode: registration.mode,
    registration_role_id: registration.mode === 'public' ? registration.roleId : null,
    email_verification_required: registration.emailVerificationRequired
  })
  toast.add({ title: 'Registration policy saved', color: 'success' })
  await refreshProjectAndAudit()
}

async function saveEnvironment() {
  await access.updateProjectEnvironment(projectId.value, {
    mode: environment.mode,
    sandbox_ttl_minutes: environment.ttlMinutes
  })
  toast.add({ title: 'Project environment saved', color: 'success' })
  await refreshProjectAndAudit()
}

async function addWebhook() {
  const webhook = await access.createWebhook(projectId.value, webhookForm)
  revealedWebhookSecret.value = { id: webhook.id, secret: webhook.secret ?? '' }
  webhookForm.url = ''
  webhookForm.events = ['identity.user.expired']
  webhookModalOpen.value = false
  await refreshProjectAndAudit()
}

async function toggleWebhook(webhook: IdentityWebhook) {
  await access.updateWebhook(projectId.value, webhook.id, {
    url: webhook.url,
    events: webhook.events,
    status: webhook.status === 'active' ? 'disabled' : 'active'
  })
  await refreshProjectAndAudit()
}

async function rotateWebhook(webhookId: string) {
  const webhook = await access.rotateWebhookSecret(projectId.value, webhookId)
  revealedWebhookSecret.value = { id: webhook.id, secret: webhook.secret ?? '' }
  await refreshProjectAndAudit()
}

async function deleteWebhook(webhookId: string) {
  await access.removeWebhook(projectId.value, webhookId)
  await refreshProjectAndAudit()
}

async function saveRolePermissions() {
  if (!selectedRole.value) return
  await access.setRolePermissions(projectId.value, selectedRole.value.id, selectedRole.value.permissionIds)
  selectedRole.value = null
  await refreshProjectAndAudit()
}

async function saveMembership() {
  if (!selectedMembership.value) return
  await access.setMembershipAccess(projectId.value, selectedMembership.value.id, {
    role_ids: selectedMembership.value.roleIds,
    permission_ids: selectedMembership.value.permissionIds,
    is_admin: selectedMembership.value.isAdmin,
    status: selectedMembership.value.status
  })
  selectedMembership.value = null
  await refreshProjectAndAudit()
}

async function removeSelectedMembership() {
  if (!selectedMembership.value) return
  await access.removeMembership(projectId.value, selectedMembership.value.id)
  selectedMembership.value = null
  await refreshProjectAndAudit()
}

function selectRole(item: IdentityRole) {
  selectedRole.value = { id: item.id, name: item.name, permissionIds: [...item.permission_ids] }
}

function selectMembership(membership: IdentityMembership) {
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
    icon="i-lucide-folder-key"
    :description="project ? `${project.slug} · ${project.mode} environment · ${project.status}` : 'Loading project access configuration.'"
    :back-to="localePath('/admin/projects')"
    back-label="Projects"
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

    <template v-else>
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
      <UAlert
        v-if="revealedWebhookSecret"
        color="warning"
        variant="soft"
        title="Store this webhook signing secret now"
        :description="`${revealedWebhookSecret.id}: ${revealedWebhookSecret.secret}`"
        icon="i-lucide-webhook"
        close
        @update:open="revealedWebhookSecret = null"
      />

      <div class="flex flex-wrap gap-2">
        <IdentityStatPill
          label="Members"
          :value="project.memberships.length"
          icon="i-lucide-users"
          description="Identities"
        />
        <IdentityStatPill
          label="Active clients"
          :value="project.clients.filter(client => client.status === 'active').length"
          icon="i-lucide-server-cog"
          color="success"
          description="Credentials"
        />
        <IdentityStatPill
          label="Roles"
          :value="project.roles.length"
          icon="i-lucide-shield-check"
          color="primary"
          description="Profiles"
        />
        <IdentityStatPill
          label="Permissions"
          :value="project.permissions.length"
          icon="i-lucide-key-round"
          color="warning"
          description="Published"
        />
      </div>

      <div class="min-w-0 overflow-x-auto">
        <UTabs
          v-model="activeTab"
          :items="tabs"
          variant="pill"
          :content="false"
          size="lg"
          class="min-w-max"
        />
      </div>

      <template v-if="activeTab === 'overview'">
        <div class="grid gap-5 lg:grid-cols-2">
          <UPageCard
            title="Project environment"
            description="Control runtime mode and sandbox lifetime directly."
            variant="subtle"
          >
            <form
              class="space-y-5"
              @submit.prevent="saveEnvironment"
            >
              <UFormField label="Mode">
                <USelect
                  v-model="environment.mode"
                  :items="[{ label: 'Live', value: 'live' }, { label: 'Sandbox', value: 'sandbox' }]"
                  class="w-full"
                />
              </UFormField>

              <UFormField
                v-if="environment.mode === 'sandbox'"
                label="Temporary account lifetime"
                description="Minutes before Identity expires and removes the sandbox identity."
              >
                <UInput
                  v-model.number="environment.ttlMinutes"
                  type="number"
                  :min="5"
                  :max="1440"
                  class="w-full"
                />
              </UFormField>

              <UAlert
                v-if="environment.mode === 'live'"
                color="success"
                variant="soft"
                icon="i-lucide-shield-check"
                title="Production safeguards enabled"
                description="Live mode uses the complete credential and verification flow."
              />

              <UButton
                type="submit"
                label="Save environment"
                icon="i-lucide-save"
              />
            </form>
          </UPageCard>

          <UPageCard
            title="Registration policy"
            description="Control how identities become members of this project."
            variant="subtle"
          >
            <form
              class="space-y-5"
              @submit.prevent="saveRegistrationPolicy"
            >
              <UFormField label="Registration">
                <USelect
                  v-model="registration.mode"
                  :items="[{ label: 'Invitation only', value: 'invite_only' }, { label: 'Public registration', value: 'public' }]"
                  class="w-full"
                />
              </UFormField>

              <UFormField
                v-if="registration.mode === 'public'"
                label="Default role"
                description="Optional role assigned to every new member."
              >
                <USelect
                  v-model="registration.roleId"
                  :items="[{ label: 'No default role', value: null }, ...project.roles.map(item => ({ label: item.name, value: item.id }))]"
                  class="w-full"
                />
              </UFormField>

              <UFormField
                label="Email verification"
                description="Choose whether new public registrations must verify ownership of their email address."
              >
                <USelect
                  v-model="registration.emailVerificationRequired"
                  :items="[
                    { label: 'Required', value: true },
                    { label: 'Not required', value: false }
                  ]"
                  class="w-full"
                />
              </UFormField>

              <UAlert
                :color="registration.mode === 'public' ? 'warning' : 'info'"
                variant="soft"
                icon="i-lucide-info"
                :title="registration.mode === 'public' ? 'Public enrollment enabled' : 'Invitation required'"
                :description="registration.mode === 'public'
                  ? (registration.emailVerificationRequired
                    ? 'Anyone using an approved client can create a project membership after verifying their email.'
                    : 'Anyone using an approved client can create a verified project membership immediately.')
                  : 'New members need a one-time invitation token.'"
              />

              <UButton
                type="submit"
                label="Save policy"
                icon="i-lucide-save"
              />
            </form>
          </UPageCard>
        </div>

        <div class="grid gap-5 lg:grid-cols-3">
          <UPageCard
            title="Client access"
            description="Confidential clients and their current state."
            variant="subtle"
          >
            <div class="space-y-4">
              <div class="flex items-center justify-between gap-4">
                <span class="text-sm text-muted">Active clients</span>
                <span class="font-semibold tabular-nums text-highlighted">{{ project.clients.filter(client => client.status === 'active').length }}</span>
              </div>
              <UButton
                label="Review clients"
                icon="i-lucide-server-cog"
                color="neutral"
                variant="outline"
                @click="() => { activeTab = 'clients' }"
              />
            </div>
          </UPageCard>

          <UPageCard
            title="Webhook delivery"
            description="Lifecycle hooks for cleanup automation."
            variant="subtle"
          >
            <div class="space-y-4">
              <div class="flex items-center justify-between gap-4">
                <span class="text-sm text-muted">Active webhooks</span>
                <span class="font-semibold tabular-nums text-highlighted">
                  {{ project.webhooks.filter(webhook => webhook.status === 'active').length }}
                </span>
              </div>
              <UButton
                label="Open webhooks"
                icon="i-lucide-webhook"
                color="neutral"
                variant="outline"
                @click="() => { activeTab = 'webhooks' }"
              />
            </div>
          </UPageCard>

          <UPageCard
            title="Permission health"
            description="Permissions that need attention."
            variant="subtle"
          >
            <div class="space-y-4">
              <div class="flex items-center justify-between gap-4">
                <span class="text-sm text-muted">Stale permissions</span>
                <span class="font-semibold tabular-nums text-highlighted">
                  {{ project.permissions.filter(permission => permission.status === 'stale').length }}
                </span>
              </div>
              <UButton
                label="Inspect access"
                icon="i-lucide-shield-check"
                color="neutral"
                variant="outline"
                @click="() => { activeTab = 'access' }"
              />
            </div>
          </UPageCard>
        </div>
      </template>

      <template v-else-if="activeTab === 'members'">
        <IdentityTableCard
          title="Project members"
          description="Effective access is the union of roles and direct permission grants."
          :count="project.memberships.length"
        >
          <template #actions>
            <UButton
              label="Invite member"
              icon="i-lucide-mail-plus"
              @click="() => { invitationModalOpen = true }"
            />
          </template>
          <UTable
            :data="project.memberships"
            :columns="memberColumns"
            empty="No members belong to this project."
            class="min-w-5xl"
          >
            <template #user-cell="{ row }">
              <div class="flex items-center gap-3 py-1">
                <UAvatar
                  :alt="row.original.user.username || row.original.user.email || row.original.user.id"
                  size="sm"
                />
                <div class="min-w-0">
                  <p class="truncate font-medium text-highlighted">
                    {{ row.original.user.username || row.original.user.email }}
                  </p>
                  <p class="truncate text-xs text-muted">
                    {{ row.original.user.email }} · authorization v{{ row.original.authorization_version }}
                  </p>
                </div>
              </div>
            </template>
            <template #roles-cell="{ row }">
              <div class="flex flex-wrap gap-1.5">
                <UBadge
                  v-if="row.original.is_admin"
                  color="primary"
                  variant="soft"
                >
                  Admin
                </UBadge>
                <UBadge
                  v-for="item in row.original.roles.slice(0, 3)"
                  :key="item"
                  color="neutral"
                  variant="soft"
                >
                  {{ item }}
                </UBadge>
                <span
                  v-if="!row.original.roles.length && !row.original.is_admin"
                  class="text-sm text-muted"
                >No roles</span>
              </div>
            </template>
            <template #permissions-cell="{ row }">
              <span class="text-sm text-muted">{{ row.original.permissions.length }} permission{{ row.original.permissions.length === 1 ? '' : 's' }}</span>
            </template>
            <template #status-cell="{ row }">
              <UBadge
                :color="row.original.status === 'active' ? 'success' : 'warning'"
                variant="soft"
                class="capitalize"
              >
                {{ row.original.status }}
              </UBadge>
            </template>
            <template #actions-cell="{ row }">
              <div class="flex justify-end">
                <UButton
                  label="Manage"
                  icon="i-lucide-settings-2"
                  color="neutral"
                  variant="ghost"
                  @click="selectMembership(row.original)"
                />
              </div>
            </template>
          </UTable>
        </IdentityTableCard>
      </template>

      <template v-else-if="activeTab === 'access'">
        <div class="grid gap-5 xl:grid-cols-2">
          <IdentityTableCard
            title="Project roles"
            description="Reusable permission sets assigned to members."
            :count="project.roles.length"
          >
            <template #actions>
              <UButton
                label="New role"
                icon="i-lucide-plus"
                size="sm"
                @click="() => { roleModalOpen = true }"
              />
            </template>
            <UTable
              :data="project.roles"
              :columns="roleColumns"
              empty="No project roles yet."
            >
              <template #name-cell="{ row }">
                <div class="py-1">
                  <p class="font-medium text-highlighted">
                    {{ row.original.name }}
                  </p>
                  <p class="text-xs text-muted">
                    {{ row.original.slug }}
                  </p>
                </div>
              </template>
              <template #permission_ids-cell="{ row }">
                <span class="text-sm text-muted">{{ row.original.permission_ids.length }} assigned</span>
              </template>
              <template #actions-cell="{ row }">
                <div class="flex justify-end">
                  <UButton
                    label="Configure"
                    icon="i-lucide-sliders-horizontal"
                    color="neutral"
                    variant="ghost"
                    @click="selectRole(row.original)"
                  />
                </div>
              </template>
            </UTable>
          </IdentityTableCard>

          <IdentityTableCard
            title="Permission catalog"
            description="Stable keys published manually or through client manifests."
            :count="project.permissions.length"
          >
            <template #actions>
              <UButton
                label="New permission"
                icon="i-lucide-plus"
                size="sm"
                @click="() => { permissionModalOpen = true }"
              />
            </template>
            <UTable
              :data="project.permissions"
              :columns="permissionColumns"
              empty="No project permissions yet."
              class="min-w-2xl"
            >
              <template #key-cell="{ row }">
                <code class="rounded-md bg-elevated px-2 py-1 text-xs">{{ row.original.key }}</code>
              </template>
              <template #name-cell="{ row }">
                <span class="text-sm text-muted">{{ row.original.name }}</span>
              </template>
              <template #source-cell="{ row }">
                <UBadge
                  color="neutral"
                  variant="soft"
                  class="capitalize"
                >
                  {{ row.original.source }}
                </UBadge>
              </template>
              <template #status-cell="{ row }">
                <UBadge
                  :color="row.original.status === 'active' ? 'success' : 'warning'"
                  variant="soft"
                  class="capitalize"
                >
                  {{ row.original.status }}
                </UBadge>
              </template>
            </UTable>
          </IdentityTableCard>
        </div>
      </template>

      <template v-else-if="activeTab === 'clients'">
        <IdentityTableCard
          title="Confidential clients"
          description="Create one credential per BFF, API, worker, or environment."
          :count="project.clients.length"
        >
          <template #actions>
            <UButton
              label="New client"
              icon="i-lucide-plus"
              @click="() => { clientModalOpen = true }"
            />
          </template>
          <UTable
            :data="project.clients"
            :columns="clientColumns"
            empty="No confidential clients yet."
            class="min-w-5xl"
          >
            <template #name-cell="{ row }">
              <div class="py-1">
                <p class="font-medium text-highlighted">
                  {{ row.original.name }}
                </p>
                <p class="font-mono text-xs text-muted">
                  {{ row.original.id }}
                </p>
              </div>
            </template>
            <template #secret_prefix-cell="{ row }">
              <code class="text-xs text-muted">{{ row.original.secret_prefix }}••••••••</code>
            </template>
            <template #last_used_at-cell="{ row }">
              <span class="whitespace-nowrap text-sm text-muted">{{ formatIdentityDate(row.original.last_used_at) }}</span>
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
            <template #actions-cell="{ row }">
              <div class="flex justify-end gap-1">
                <UButton
                  :label="row.original.status === 'active' ? 'Disable' : 'Enable'"
                  :icon="row.original.status === 'active' ? 'i-lucide-ban' : 'i-lucide-circle-check'"
                  color="neutral"
                  variant="ghost"
                  @click="toggleClient(row.original)"
                />
                <UButton
                  label="Rotate"
                  icon="i-lucide-refresh-cw"
                  color="neutral"
                  variant="outline"
                  @click="rotateClient(row.original.id)"
                />
              </div>
            </template>
          </UTable>
        </IdentityTableCard>
      </template>

      <template v-else-if="activeTab === 'hosted-applications'">
        <IdentityTableCard
          title="Hosted applications"
          description="Give every product a secure Identity flow, its own client binding, redirect policy, and hosted-page brand."
          :count="project.hosted_applications.length"
        >
          <template #actions>
            <UButton
              label="New hosted app"
              icon="i-lucide-plus"
              @click="openHostedApplication()"
            />
          </template>
          <div class="mb-5 flex gap-3 rounded-xl border border-default bg-elevated/40 p-4 text-sm">
            <UIcon
              name="i-lucide-shield-check"
              class="mt-0.5 size-5 shrink-0 text-primary"
            />
            <div>
              <p class="font-medium text-highlighted">
                Identity hosts the sign-in experience; your application keeps its confidential client secret.
              </p>
              <p class="mt-1 text-muted">
                Configure only the allowed landing and callback URLs here, then tailor the page users see during authentication.
              </p>
            </div>
          </div>
          <div
            v-if="project.hosted_applications.length"
            class="grid gap-4 xl:grid-cols-2"
          >
            <article
              v-for="application in project.hosted_applications"
              :key="application.id"
              class="overflow-hidden rounded-xl border border-default bg-default shadow-sm"
            >
              <div class="flex items-start justify-between gap-4 border-b border-default p-5">
                <div class="flex min-w-0 items-center gap-3">
                  <div
                    class="grid size-11 shrink-0 place-items-center rounded-xl bg-muted text-sm font-bold text-highlighted"
                    :style="application.appearance.accent_color ? { borderColor: application.appearance.accent_color, borderWidth: '2px' } : {}"
                  >
                    <img
                      v-if="application.appearance.logo_url"
                      :src="application.appearance.logo_url"
                      :alt="`${application.name} logo`"
                      class="size-8 rounded object-contain"
                    >
                    <span v-else>{{ application.name.slice(0, 2).toUpperCase() }}</span>
                  </div>
                  <div class="min-w-0">
                    <p class="truncate font-semibold text-highlighted">
                      {{ application.name }}
                    </p>
                    <p class="truncate font-mono text-xs text-muted">
                      {{ application.key }}
                    </p>
                  </div>
                </div>
                <UBadge
                  :color="application.status === 'active' ? 'success' : 'neutral'"
                  variant="soft"
                  class="capitalize"
                >
                  {{ application.status }}
                </UBadge>
              </div>
              <div class="grid gap-3 p-5 text-sm">
                <div
                  class="rounded-lg border border-default p-3"
                  :style="{
                    'background': {
                      identity: 'linear-gradient(135deg, #f6f7fb, #e7ebf5)',
                      slate: 'linear-gradient(135deg, #e9eff7, #cad7e8)',
                      indigo: 'linear-gradient(135deg, #eef0ff, #d5dcff)',
                      emerald: 'linear-gradient(135deg, #e9f8f2, #c7ecdc)',
                      sunset: 'linear-gradient(135deg, #fff2ea, #ffd9cd)'
                    }[application.appearance.background_preset],
                    '--hosted-app-accent': application.appearance.accent_color || '#3157d5'
                  }"
                >
                  <div class="rounded-md border border-white/70 bg-white/85 p-3 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">
                      {{ application.name }} · Sign in
                    </p>
                    <div class="mt-3 h-2 w-3/5 rounded bg-muted" />
                    <div class="mt-2 h-2 w-4/5 rounded bg-muted" />
                    <div
                      class="mt-3 h-7 rounded"
                      style="background: var(--hosted-app-accent)"
                    />
                  </div>
                </div>
                <dl class="grid gap-3 sm:grid-cols-2">
                  <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">
                      Application URL
                    </dt>
                    <dd
                      class="mt-1 truncate font-mono text-xs text-highlighted"
                      :title="application.application_url"
                    >
                      {{ application.application_url }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">
                      Callback URL
                    </dt>
                    <dd
                      class="mt-1 truncate font-mono text-xs text-highlighted"
                      :title="application.callback_url"
                    >
                      {{ application.callback_url }}
                    </dd>
                  </div>
                </dl>
                <div class="flex items-center justify-between gap-3 border-t border-default pt-3">
                  <p
                    class="truncate font-mono text-xs text-muted"
                    :title="application.primary_client_id"
                  >
                    Client · {{ application.primary_client_id }}
                  </p>
                  <UBadge
                    color="neutral"
                    variant="subtle"
                  >
                    {{ application.appearance.background_preset }} theme
                  </UBadge>
                  <div class="flex gap-1">
                    <UButton
                      label="Configure"
                      icon="i-lucide-settings-2"
                      color="neutral"
                      variant="outline"
                      @click="openHostedApplication(application)"
                    />
                    <UButton
                      icon="i-lucide-trash-2"
                      color="error"
                      variant="ghost"
                      aria-label="Delete hosted application"
                      @click="deleteHostedApplication(application.id)"
                    />
                  </div>
                </div>
              </div>
            </article>
          </div>
          <p
            v-else
            class="py-8 text-center text-sm text-muted"
          >
            No hosted applications are configured for this project.
          </p>
        </IdentityTableCard>
      </template>

      <template v-else-if="activeTab === 'webhooks'">
        <IdentityTableCard
          title="Cleanup webhooks"
          description="Signed lifecycle events allow consuming services to erase user-owned data."
          :count="project.webhooks.length"
        >
          <template #actions>
            <UButton
              label="Add webhook"
              icon="i-lucide-plus"
              @click="() => { webhookModalOpen = true }"
            />
          </template>
          <UTable
            :data="project.webhooks"
            :columns="webhookColumns"
            empty="No cleanup webhooks configured."
            class="min-w-6xl"
          >
            <template #url-cell="{ row }">
              <div class="max-w-md py-1">
                <p class="truncate font-medium text-highlighted">
                  {{ row.original.url }}
                </p>
                <p class="text-xs text-muted">
                  Secret {{ row.original.secret_prefix }}••••••••
                </p>
              </div>
            </template>
            <template #events-cell="{ row }">
              <div class="flex flex-wrap gap-1.5">
                <UBadge
                  v-for="event in row.original.events"
                  :key="event"
                  color="neutral"
                  variant="soft"
                >
                  {{ event.replace('identity.user.', '') }}
                </UBadge>
              </div>
            </template>
            <template #last_delivered_at-cell="{ row }">
              <span class="whitespace-nowrap text-sm text-muted">{{ formatIdentityDate(row.original.last_delivered_at) }}</span>
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
            <template #actions-cell="{ row }">
              <div class="flex justify-end gap-1">
                <UButton
                  :icon="row.original.status === 'active' ? 'i-lucide-pause' : 'i-lucide-play'"
                  color="neutral"
                  variant="ghost"
                  :aria-label="row.original.status === 'active' ? 'Disable webhook' : 'Enable webhook'"
                  @click="toggleWebhook(row.original)"
                />
                <UButton
                  icon="i-lucide-refresh-cw"
                  color="neutral"
                  variant="ghost"
                  aria-label="Rotate webhook secret"
                  @click="rotateWebhook(row.original.id)"
                />
                <UButton
                  icon="i-lucide-trash-2"
                  color="error"
                  variant="ghost"
                  aria-label="Delete webhook"
                  @click="deleteWebhook(row.original.id)"
                />
              </div>
            </template>
          </UTable>
        </IdentityTableCard>
      </template>

      <template v-else-if="activeTab === 'audit'">
        <IdentityShellState
          v-if="auditRequest.status.value === 'pending'"
          state="loading"
          title="Loading audit history"
        />
        <IdentityShellState
          v-else-if="auditRequest.error.value"
          state="error"
          title="Unable to load audit history"
          :description="auditRequest.error.value.statusMessage || 'The audit stream could not be loaded.'"
          @retry="auditRequest.refresh()"
        />
        <IdentityTableCard
          v-else
          title="Audit history"
          description="Authentication and access-management changes for this project."
          :count="auditEvents.length"
        >
          <UTable
            :data="visibleAuditEvents"
            :columns="auditColumns"
            empty="No audit events recorded yet."
            class="min-w-6xl"
          >
            <template #event-cell="{ row }">
              <code class="text-xs text-highlighted">{{ row.original.event }}</code>
            </template>
            <template #target_type-cell="{ row }">
              <div class="max-w-xs">
                <p class="text-sm">
                  {{ row.original.target_type || 'project' }}
                </p><p class="truncate font-mono text-xs text-muted">
                  {{ row.original.target_id || projectId }}
                </p>
              </div>
            </template>
            <template #actor_user_id-cell="{ row }">
              <span class="font-mono text-xs text-muted">{{ row.original.actor_user_id || 'System' }}</span>
            </template>
            <template #ip_address-cell="{ row }">
              <span class="font-mono text-xs text-muted">{{ row.original.ip_address || '—' }}</span>
            </template>
            <template #created_at-cell="{ row }">
              <time class="whitespace-nowrap text-sm text-muted">{{ formatIdentityDate(row.original.created_at) }}</time>
            </template>
          </UTable>
          <template
            v-if="hasMoreAuditEvents"
            #footer
          >
            <p class="text-sm text-muted">
              Showing {{ visibleAuditEvents.length }} of {{ auditEvents.length }} events
            </p>
            <UButton
              label="Load more"
              icon="i-lucide-chevron-down"
              color="neutral"
              variant="ghost"
              @click="() => { auditVisibleCount += 20 }"
            />
          </template>
        </IdentityTableCard>
        <div
          ref="auditSentinel"
          class="h-px"
          aria-hidden="true"
        />
      </template>

      <UModal
        v-model:open="clientModalOpen"
        title="Create confidential client"
        description="The secret is shown once after creation."
      >
        <template #body>
          <form
            class="space-y-4"
            @submit.prevent="addClient"
          >
            <UFormField
              label="Client name"
              required
            >
              <UInput
                v-model="clientName"
                autofocus
                placeholder="My client"
                class="w-full"
              />
            </UFormField><div class="flex justify-end gap-2">
              <UButton
                label="Cancel"
                color="neutral"
                variant="ghost"
                @click="() => { clientModalOpen = false }"
              /><UButton
                type="submit"
                label="Create client"
                icon="i-lucide-plus"
              />
            </div>
          </form>
        </template>
      </UModal>
      <UModal
        v-model:open="hostedApplicationModalOpen"
        :title="hostedApplication.id ? 'Configure hosted application' : 'Create hosted application'"
        description="A hosted application defines the secure handoff between Identity and one product. Client secrets remain in that product's BFF."
        :ui="{ content: 'sm:max-w-6xl' }"
      >
        <template #body>
          <form
            class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]"
            @submit.prevent="saveHostedApplication"
          >
            <div class="space-y-5">
              <section class="rounded-xl border border-default">
                <div class="border-b border-default px-5 py-4">
                  <div class="flex items-center gap-2">
                    <UIcon
                      name="i-lucide-app-window"
                      class="size-4 text-primary"
                    />
                    <h3 class="font-semibold text-highlighted">
                      Application identity
                    </h3>
                  </div>
                  <p class="mt-1 text-sm text-muted">
                    These values identify the product in hosted URLs and to its users.
                  </p>
                </div>
                <div class="grid gap-4 p-5 sm:grid-cols-2">
                  <UFormField
                    label="Application name"
                    required
                    class="sm:col-span-2"
                  >
                    <UInput
                      v-model="hostedApplication.name"
                      autofocus
                      placeholder="Job Tracker"
                      class="w-full"
                    />
                  </UFormField>
                  <UFormField
                    label="Application key"
                    description="Used by the consuming BFF to start hosted authentication."
                    required
                    class="sm:col-span-2"
                  >
                    <UInput
                      v-model="hostedApplication.key"
                      :disabled="Boolean(hostedApplication.id)"
                      placeholder="job-tracker"
                      class="w-full font-mono"
                    />
                  </UFormField>
                </div>
              </section>

              <section class="rounded-xl border border-default">
                <div class="border-b border-default px-5 py-4">
                  <div class="flex items-center gap-2">
                    <UIcon
                      name="i-lucide-shield-check"
                      class="size-4 text-primary"
                    />
                    <h3 class="font-semibold text-highlighted">
                      Sign-in and legal
                    </h3>
                  </div>
                  <p class="mt-1 text-sm text-muted">
                    Select the hosted sign-in options. Legal links are shown only on the hosted pages for this application.
                  </p>
                </div>
                <div class="space-y-4 p-5">
                  <UCheckbox
                    v-model="hostedApplication.googleEnabled"
                    label="Offer Google sign-in"
                    description="Requires Google OAuth to be configured on the Identity host before it is available to users."
                  />
                  <UCheckbox
                    v-model="hostedApplication.termsRequired"
                    label="Require acceptance of terms when creating an account"
                    description="Identity records the accepted terms URL with the new account."
                  />
                  <div class="grid gap-4 sm:grid-cols-2">
                    <UFormField
                      label="Terms of Service URL"
                      :required="hostedApplication.termsRequired"
                    >
                      <UInput
                        v-model="hostedApplication.termsUrl"
                        type="url"
                        placeholder="https://app.example.com/legal/terms"
                        class="w-full font-mono"
                      />
                    </UFormField>
                    <UFormField label="Privacy Policy URL">
                      <UInput
                        v-model="hostedApplication.privacyUrl"
                        type="url"
                        placeholder="https://app.example.com/legal/privacy"
                        class="w-full font-mono"
                      />
                    </UFormField>
                  </div>
                </div>
              </section>

              <section class="rounded-xl border border-default">
                <div class="border-b border-default px-5 py-4">
                  <div class="flex items-center gap-2">
                    <UIcon
                      name="i-lucide-key-round"
                      class="size-4 text-primary"
                    />
                    <h3 class="font-semibold text-highlighted">
                      Client binding
                    </h3>
                  </div>
                  <p class="mt-1 text-sm text-muted">
                    Bind the confidential clients Identity uses internally. Their secrets never enter this form.
                  </p>
                </div>
                <div class="grid gap-4 p-5">
                  <UFormField
                    label="Primary client"
                    description="An active client from this project."
                    required
                  >
                    <USelect
                      v-model="hostedApplication.primaryClientId"
                      :items="primaryClientOptions"
                      placeholder="Select a client"
                      class="w-full"
                    />
                  </UFormField>
                  <UFormField
                    label="Sandbox client ID"
                    description="Optional active BFF client from a sandbox project. Linking it enables the temporary demo-account button on this hosted application."
                  >
                    <UInput
                      v-model="hostedApplication.sandboxClientId"
                      placeholder="Optional sandbox client UUID"
                      class="w-full font-mono"
                    />
                  </UFormField>
                </div>
              </section>

              <section class="rounded-xl border border-default">
                <div class="border-b border-default px-5 py-4">
                  <div class="flex items-center gap-2">
                    <UIcon
                      name="i-lucide-route"
                      class="size-4 text-primary"
                    />
                    <h3 class="font-semibold text-highlighted">
                      Redirect policy
                    </h3>
                  </div>
                  <p class="mt-1 text-sm text-muted">
                    Identity redirects only to these approved product endpoints after authentication.
                  </p>
                </div>
                <div class="grid gap-4 p-5">
                  <UFormField
                    label="Application URL"
                    required
                  >
                    <UInput
                      v-model="hostedApplication.applicationUrl"
                      type="url"
                      placeholder="https://app.example.com/dashboard"
                      class="w-full font-mono"
                    />
                  </UFormField>
                  <UFormField
                    label="Callback URL"
                    required
                  >
                    <UInput
                      v-model="hostedApplication.callbackUrl"
                      type="url"
                      placeholder="https://app.example.com/api/auth/callback"
                      class="w-full font-mono"
                    />
                  </UFormField>
                </div>
              </section>

              <section class="rounded-xl border border-default">
                <div class="border-b border-default px-5 py-4">
                  <div class="flex items-center gap-2">
                    <UIcon
                      name="i-lucide-palette"
                      class="size-4 text-primary"
                    />
                    <h3 class="font-semibold text-highlighted">
                      Hosted page appearance
                    </h3>
                  </div>
                  <p class="mt-1 text-sm text-muted">
                    Make the secure Identity pages recognizably part of this product without custom HTML or CSS.
                  </p>
                </div>
                <div class="space-y-4 p-5">
                  <div class="flex items-center gap-3">
                    <div class="grid size-12 shrink-0 place-items-center rounded-xl border border-default bg-muted">
                      <img
                        v-if="hostedApplicationPreviewLogo"
                        :src="hostedApplicationPreviewLogo"
                        :alt="`${hostedApplication.name || 'Application'} logo preview`"
                        class="size-9 rounded object-contain"
                      >
                      <UIcon
                        v-else
                        name="i-lucide-image"
                        class="size-5 text-muted"
                      />
                    </div>
                    <div class="min-w-0 flex-1">
                      <label class="block text-sm font-medium text-highlighted">
                        Logo
                      </label>
                      <input
                        type="file"
                        accept="image/png,image/jpeg,image/webp,image/svg+xml"
                        class="mt-1 block w-full text-sm"
                        @change="chooseHostedApplicationLogo"
                      >
                      <p class="mt-1 text-xs text-muted">
                        PNG, JPEG, WebP, or SVG up to 2 MB.
                      </p>
                    </div>
                    <UButton
                      v-if="hostedApplicationPreviewLogo"
                      label="Remove"
                      color="error"
                      variant="ghost"
                      @click="() => { hostedApplicationLogo = null; hostedApplicationLogoPreview = null; removeHostedApplicationLogo = true }"
                    />
                  </div>
                  <UFormField
                    label="Welcome text"
                    description="Optional short message shown below the product-specific sign-in introduction."
                  >
                    <UTextarea
                      v-model="hostedApplication.welcomeText"
                      :rows="2"
                      maxlength="280"
                      class="w-full"
                    />
                  </UFormField>
                  <div class="grid gap-4 sm:grid-cols-2">
                    <UFormField label="Accent colour">
                      <UInput
                        v-model="hostedApplication.accentColor"
                        type="color"
                        class="w-full"
                      />
                    </UFormField>
                    <UFormField label="Background">
                      <USelect
                        v-model="hostedApplication.backgroundPreset"
                        :items="hostedApplicationBackgroundOptions"
                        class="w-full"
                      />
                    </UFormField>
                  </div>
                </div>
              </section>

              <section
                v-if="hostedApplication.id"
                class="rounded-xl border border-default"
              >
                <div class="flex items-center justify-between gap-4 px-5 py-4">
                  <div>
                    <h3 class="font-semibold text-highlighted">
                      Availability
                    </h3>
                    <p class="mt-1 text-sm text-muted">
                      Disabled applications cannot begin a new hosted authentication flow.
                    </p>
                  </div>
                  <USelect
                    v-model="hostedApplication.status"
                    :items="[{ label: 'Active', value: 'active' }, { label: 'Disabled', value: 'disabled' }]"
                    class="w-32"
                  />
                </div>
              </section>
            </div>

            <aside class="h-fit space-y-4 lg:sticky lg:top-4">
              <div class="rounded-xl border border-default bg-elevated/40 p-4">
                <p class="text-sm font-semibold text-highlighted">
                  Hosted page preview
                </p>
                <p class="mt-1 text-xs text-muted">
                  A live representation of the default Identity sign-in page.
                </p>
                <div
                  class="mt-4 rounded-xl border border-default p-3"
                  :style="hostedApplicationPreviewStyle"
                >
                  <div class="rounded-lg border border-white/70 bg-white/90 p-4 shadow-sm">
                    <div class="flex items-center gap-2">
                      <div class="grid size-7 place-items-center rounded bg-muted">
                        <img
                          v-if="hostedApplicationPreviewLogo"
                          :src="hostedApplicationPreviewLogo"
                          alt=""
                          class="size-5 object-contain"
                        >
                        <span
                          v-else
                          class="text-[10px] font-bold text-muted"
                        >{{ (hostedApplication.name || 'App').slice(0, 2).toUpperCase() }}</span>
                      </div>
                      <p class="truncate text-[10px] font-bold uppercase tracking-wider text-slate-500">
                        {{ hostedApplication.name || 'Your application' }}
                      </p>
                    </div>
                    <h4 class="mt-4 text-base font-semibold text-slate-900">
                      Sign in
                    </h4>
                    <p class="mt-1 text-xs leading-5 text-slate-500">
                      Use your {{ hostedApplication.name || 'application' }} account to continue.
                    </p>
                    <p
                      v-if="hostedApplication.welcomeText"
                      class="mt-2 text-xs leading-5 text-slate-500"
                    >
                      {{ hostedApplication.welcomeText }}
                    </p>
                    <div class="mt-4 h-8 rounded-md border border-slate-200 bg-white" />
                    <div class="mt-2 h-8 rounded-md border border-slate-200 bg-white" />
                    <div
                      class="mt-3 h-8 rounded-md"
                      style="background: var(--hosted-app-accent)"
                    />
                  </div>
                </div>
              </div>
              <div class="rounded-xl border border-default p-4 text-sm">
                <div class="flex gap-2">
                  <UIcon
                    name="i-lucide-lock-keyhole"
                    class="mt-0.5 size-4 shrink-0 text-primary"
                  />
                  <p class="text-muted">
                    Identity controls the form, session, validation, and redirects. Appearance settings cannot inject custom code.
                  </p>
                </div>
              </div>
            </aside>

            <div class="flex justify-end gap-2 border-t border-default pt-5 lg:col-span-2">
              <UButton
                label="Cancel"
                color="neutral"
                variant="ghost"
                @click="() => { hostedApplicationModalOpen = false }"
              />
              <UButton
                type="submit"
                :label="hostedApplication.id ? 'Save application' : 'Create application'"
                icon="i-lucide-save"
              />
            </div>
          </form>
        </template>
      </UModal>
      <UModal
        v-model:open="invitationModalOpen"
        title="Invite a member"
        description="Create a one-time invitation for this project."
      >
        <template #body>
          <form
            class="space-y-4"
            @submit.prevent="sendInvitation"
          >
            <UFormField
              label="Email"
              required
            >
              <UInput
                v-model="invitation.email"
                autofocus
                type="email"
                class="w-full"
              />
            </UFormField><UCheckbox
              v-model="invitation.is_admin"
              label="Project administrator"
            /><div class="flex justify-end gap-2">
              <UButton
                label="Cancel"
                color="neutral"
                variant="ghost"
                @click="() => { invitationModalOpen = false }"
              /><UButton
                type="submit"
                label="Create invitation"
                icon="i-lucide-mail-plus"
              />
            </div>
          </form>
        </template>
      </UModal>
      <UModal
        v-model:open="roleModalOpen"
        title="Create project role"
        description="Roles collect stable permission keys."
      >
        <template #body>
          <form
            class="space-y-4"
            @submit.prevent="addRole"
          >
            <UFormField
              label="Name"
              required
            >
              <UInput
                v-model="role.name"
                autofocus
                placeholder="Editor"
                class="w-full"
              />
            </UFormField><UFormField
              label="Slug"
              required
            >
              <UInput
                v-model="role.slug"
                placeholder="editor"
                class="w-full"
              />
            </UFormField><UFormField label="Description">
              <UTextarea
                v-model="role.description"
                class="w-full"
              />
            </UFormField><div class="flex justify-end gap-2">
              <UButton
                label="Cancel"
                color="neutral"
                variant="ghost"
                @click="() => { roleModalOpen = false }"
              /><UButton
                type="submit"
                label="Create role"
                icon="i-lucide-plus"
              />
            </div>
          </form>
        </template>
      </UModal>
      <UModal
        v-model:open="permissionModalOpen"
        title="Create permission"
        description="Use a stable namespaced key shared with consuming services."
      >
        <template #body>
          <form
            class="space-y-4"
            @submit.prevent="addPermission"
          >
            <UFormField
              label="Permission key"
              required
            >
              <UInput
                v-model="permissionForm.key"
                autofocus
                placeholder="documents.read"
                class="w-full"
              />
            </UFormField><UFormField label="Name">
              <UInput
                v-model="permissionForm.name"
                placeholder="Read documents"
                class="w-full"
              />
            </UFormField><UFormField label="Description">
              <UTextarea
                v-model="permissionForm.description"
                class="w-full"
              />
            </UFormField><div class="flex justify-end gap-2">
              <UButton
                label="Cancel"
                color="neutral"
                variant="ghost"
                @click="() => { permissionModalOpen = false }"
              /><UButton
                type="submit"
                label="Create permission"
                icon="i-lucide-plus"
              />
            </div>
          </form>
        </template>
      </UModal>
      <UModal
        v-model:open="webhookModalOpen"
        title="Add cleanup webhook"
        description="Identity signs every lifecycle event delivered to this endpoint."
      >
        <template #body>
          <form
            class="space-y-4"
            @submit.prevent="addWebhook"
          >
            <UFormField
              label="Endpoint URL"
              required
            >
              <UInput
                v-model="webhookForm.url"
                autofocus
                type="url"
                placeholder="https://api.example.com/api/webhooks/identity"
                class="w-full"
              />
            </UFormField><UFormField label="Events">
              <UCheckboxGroup
                v-model="webhookForm.events"
                :items="[{ label: 'Temporary user expired', value: 'identity.user.expired' }, { label: 'User deletion requested', value: 'identity.user.deletion_requested' }]"
              />
            </UFormField><div class="flex justify-end gap-2">
              <UButton
                label="Cancel"
                color="neutral"
                variant="ghost"
                @click="() => { webhookModalOpen = false }"
              /><UButton
                type="submit"
                label="Add webhook"
                icon="i-lucide-plus"
              />
            </div>
          </form>
        </template>
      </UModal>
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
            <div class="max-h-96 overflow-y-auto rounded-xl border border-default p-3">
              <UCheckboxGroup
                v-model="selectedRole.permissionIds"
                :items="projectPermissionOptions"
                class="grid gap-3 sm:grid-cols-2"
              />
            </div><div class="flex justify-end">
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
            /><UFormField label="Status">
              <USelect
                v-model="selectedMembership.status"
                :items="[{ label: 'Active', value: 'active' }, { label: 'Suspended', value: 'suspended' }]"
                class="w-full"
              />
            </UFormField><div>
              <p class="mb-3 text-sm font-medium">
                Roles
              </p><UCheckboxGroup
                v-model="selectedMembership.roleIds"
                :items="projectRoleOptions"
                class="grid gap-3 sm:grid-cols-2"
              />
            </div><div>
              <p class="mb-3 text-sm font-medium">
                Direct permission grants
              </p><UCheckboxGroup
                v-model="selectedMembership.permissionIds"
                :items="projectPermissionOptions"
                class="grid gap-3 sm:grid-cols-2"
              />
            </div><div class="flex justify-between gap-3">
              <UButton
                type="button"
                label="Remove membership"
                color="error"
                variant="outline"
                icon="i-lucide-user-minus"
                @click="removeSelectedMembership"
              /><UButton
                type="submit"
                label="Save access"
                icon="i-lucide-save"
              />
            </div>
          </form>
        </template>
      </UModal>
    </template>
  </IdentityPanel>
</template>
