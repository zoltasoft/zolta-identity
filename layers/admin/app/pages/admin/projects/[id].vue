<script setup lang="ts">
import type { TableColumn } from '@nuxt/ui'
import type {
  IdentityAccessCatalog,
  IdentityAccessDependencyConflict,
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
type AccessRoleRow = {
  id: string
  name: string
  slug: string
  permissionCount: number
  global: boolean
  availableCatalog: boolean
  projectRole: IdentityRole | null
}
type AccessDeletionTarget
  = { kind: 'role', resource: IdentityRole }
    | { kind: 'permission', resource: IdentityPermission }

const route = useRoute()
const router = useRouter()
const localePath = useLocalePath()
const projectId = computed(() => String(route.params.id))
const access = useIdentityAccess()
const { data: project, status, error, refresh } = await access.project(projectId)
const { data: session } = await access.session()
const catalogRequest = await access.projectAccessCatalog()
const auditRequest = await access.audit(projectId, { immediate: false })
const toast = useToast()
const routeTab = Array.isArray(route.query.tab) ? route.query.tab[0] : route.query.tab
const validTabs: ProjectTab[] = ['overview', 'members', 'access', 'clients', 'hosted-applications', 'webhooks', 'audit']
const activeTab = ref<ProjectTab>(validTabs.includes(routeTab as ProjectTab) ? routeTab as ProjectTab : 'overview')
const auditLoaded = ref(false)
const auditVisibleCount = ref(20)
const auditSentinel = ref<HTMLElement | null>(null)
const clientModalOpen = ref(false)
const clientDeletionModalOpen = ref(false)
const clientDeletionConfirmation = ref('')
const clientDeletionSaving = ref(false)
const selectedClientForDeletion = ref<IdentityClient | null>(null)
const hostedApplicationModalOpen = ref(false)
const invitationModalOpen = ref(false)
const roleModalOpen = ref(false)
const permissionModalOpen = ref(false)
const webhookModalOpen = ref(false)
const deletionModalOpen = ref(false)
const deletionConfirmation = ref('')
const deletionSaving = ref(false)
const suspensionModalOpen = ref(false)
const suspensionConfirmation = ref('')
const suspensionSaving = ref(false)
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
const selectedAccessDeletion = ref<AccessDeletionTarget | null>(null)
const accessDeletionConfirmation = ref('')
const accessDeletionSaving = ref(false)
const accessDeletionServerMessage = ref('')
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
const catalog = computed<IdentityAccessCatalog | null>(() => catalogRequest?.data.value ?? null)
const projectPermissions = computed(() => project.value?.permissions ?? [])
const configurableProjectPermissions = computed(() => projectPermissions.value.filter(item => item.catalog_origin !== 'imported'))
const roleRows = computed<AccessRoleRow[]>(() => {
  const projectCatalogRoleIds = new Set((project.value?.roles ?? [])
    .filter(item => item.catalog_role_id)
    .map(item => item.catalog_role_id))
  const reusableRoles = (catalog.value?.roles ?? [])
    .filter(item => !projectCatalogRoleIds.has(item.id))
    .map(item => ({ id: `catalog:${item.id}`, name: item.name, slug: item.slug, permissionCount: item.permission_ids.length, global: true, availableCatalog: true, projectRole: null }))
  const localRoles = (project.value?.roles ?? [])
    .map(item => ({ id: `project:${item.id}`, name: item.name, slug: item.slug, permissionCount: item.permission_ids.length, global: item.catalog_role_id !== null, availableCatalog: false, projectRole: item }))

  return [...reusableRoles, ...localRoles]
})
const registrationRoleOptions = computed(() => [
  { label: 'No default role', value: null },
  ...(catalog.value?.roles ?? []).map(item => ({ label: `${item.name} · Catalog`, value: `catalog:${item.id}` })),
  ...(project.value?.roles ?? [])
    .filter(item => item.catalog_origin !== 'imported')
    .map(item => ({ label: item.name, value: `project:${item.id}` }))
])
const rolePermissionChoices = computed(() => {
  const publishedCatalogPermissionIds = new Set((project.value?.permissions ?? [])
    .filter(item => item.catalog_origin === 'published' && item.catalog_permission_id)
    .map(item => item.catalog_permission_id))
  return [
    ...(catalog.value?.permissions ?? [])
      .filter(item => !publishedCatalogPermissionIds.has(item.id))
      .map(item => ({ value: `catalog:${item.id}`, label: item.key, global: true })),
    ...configurableProjectPermissions.value.map(item => ({ value: `project:${item.id}`, label: item.key, global: item.catalog_permission_id !== null }))
  ]
})
const accessDeletionMemberships = computed(() => {
  const target = selectedAccessDeletion.value
  if (!target || !project.value) return []
  return project.value.memberships.filter(membership => target.kind === 'role'
    ? membership.role_ids.includes(target.resource.id)
    : membership.direct_permission_ids.includes(target.resource.id))
})
const accessDeletionRoles = computed(() => {
  const target = selectedAccessDeletion.value
  if (!target || target.kind !== 'permission' || !project.value) return []
  return project.value.roles.filter(role => role.permission_ids.includes(target.resource.id))
})
const accessDeletionIsRegistrationDefault = computed(() => {
  const target = selectedAccessDeletion.value
  return target?.kind === 'role' && project.value?.registration_role_id === target.resource.id
})
const accessDeletionManifestClient = computed(() => {
  const target = selectedAccessDeletion.value
  if (!target || target.kind !== 'permission' || target.resource.source !== 'manifest' || target.resource.status !== 'active') return null
  return project.value?.clients.find(client => client.id === target.resource.source_client_id) ?? null
})
const accessDeletionBlocked = computed(() => accessDeletionMemberships.value.length > 0
  || accessDeletionRoles.value.length > 0
  || accessDeletionIsRegistrationDefault.value
  || accessDeletionManifestClient.value !== null)
const accessDeletionExpectedConfirmation = computed(() => {
  const target = selectedAccessDeletion.value
  return target?.kind === 'role' ? target.resource.slug : target?.resource.key ?? ''
})
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
const canManageDeletion = computed(() => session.value?.isSystemAdmin === true)
const projectChangesLocked = computed(() => project.value?.status !== 'active')
const projectChangesLockMessage = computed(() => project.value?.status === 'suspended'
  ? 'This project is suspended. Reactivate it before making configuration changes.'
  : 'This project is scheduled for deletion. Cancel deletion before making configuration changes.')

const memberColumns: TableColumn<IdentityMembership>[] = [
  { accessorKey: 'user', header: 'Member' },
  { accessorKey: 'roles', header: 'Roles' },
  { accessorKey: 'permissions', header: 'Effective permissions' },
  { accessorKey: 'status', header: 'Status' },
  { id: 'actions', header: '' }
]
const roleColumns: TableColumn<AccessRoleRow>[] = [
  { accessorKey: 'name', header: 'Role' },
  { accessorKey: 'permissionCount', header: 'Permissions' },
  { id: 'actions', header: '' }
]
const permissionColumns: TableColumn<IdentityPermission>[] = [
  { accessorKey: 'key', header: 'Permission key' },
  { accessorKey: 'name', header: 'Name' },
  { accessorKey: 'source', header: 'Source' },
  { accessorKey: 'status', header: 'Status' },
  { id: 'actions', header: '' }
]
const clientColumns: TableColumn<IdentityClient>[] = [
  { accessorKey: 'name', header: 'Client' },
  { accessorKey: 'secret_prefix', header: 'Credential' },
  { accessorKey: 'last_used_at', header: 'Last used' },
  { accessorKey: 'status', header: 'Status' },
  { id: 'actions', header: '' }
]
const selectedClientDependencies = computed(() => (project.value?.hosted_applications ?? []).filter(application => (
  application.primary_client_id === selectedClientForDeletion.value?.id
  || application.sandbox_client_id === selectedClientForDeletion.value?.id
)))
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
  const registrationRole = value.roles.find(item => item.id === value.registration_role_id)
  registration.roleId = registrationRole?.catalog_origin === 'imported' && registrationRole.catalog_role_id
    ? `catalog:${registrationRole.catalog_role_id}`
    : value.registration_role_id ? `project:${value.registration_role_id}` : null
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

function openClientDeletion(client: IdentityClient) {
  selectedClientForDeletion.value = client
  clientDeletionConfirmation.value = ''
  clientDeletionModalOpen.value = true
}

function clientDependencies(client: IdentityClient): string[] {
  return (project.value?.hosted_applications ?? [])
    .filter(application => application.primary_client_id === client.id || application.sandbox_client_id === client.id)
    .map(application => application.name)
}

async function deleteClient() {
  const client = selectedClientForDeletion.value
  if (!client) return
  clientDeletionSaving.value = true
  try {
    await access.removeClient(projectId.value, client.id, clientDeletionConfirmation.value)
    clientDeletionModalOpen.value = false
    selectedClientForDeletion.value = null
    clientDeletionConfirmation.value = ''
    toast.add({ title: 'Client deleted', description: 'Its credentials have been revoked and its name is available again.', color: 'success' })
    await refreshProjectAndAudit()
  } finally {
    clientDeletionSaving.value = false
  }
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

function openAccessDeletion(target: AccessDeletionTarget) {
  selectedAccessDeletion.value = target
  accessDeletionConfirmation.value = ''
  accessDeletionServerMessage.value = ''
}

function accessDependencyConflict(error: unknown): IdentityAccessDependencyConflict | null {
  const candidate = error as {
    data?: { errors?: { public?: IdentityAccessDependencyConflict } }
    response?: { _data?: { errors?: { public?: IdentityAccessDependencyConflict } } }
  }
  const conflict = candidate.data?.errors?.public ?? candidate.response?._data?.errors?.public
  return conflict?.code === 'identity.access_dependency_conflict' ? conflict : null
}

async function deleteSelectedAccessResource() {
  const target = selectedAccessDeletion.value
  if (!target || accessDeletionBlocked.value) return
  accessDeletionSaving.value = true
  accessDeletionServerMessage.value = ''
  try {
    if (target.kind === 'role') {
      await access.removeRole(projectId.value, target.resource.id, accessDeletionConfirmation.value)
    } else {
      await access.removePermission(projectId.value, target.resource.id, accessDeletionConfirmation.value)
    }
    toast.add({ title: target.kind === 'role' ? 'Role deleted' : 'Permission deleted', color: 'success' })
    selectedAccessDeletion.value = null
    accessDeletionConfirmation.value = ''
    await refreshProjectAndAudit()
  } catch (error: unknown) {
    const conflict = accessDependencyConflict(error)
    if (!conflict) throw error
    accessDeletionServerMessage.value = conflict.message
    await refresh()
  } finally {
    accessDeletionSaving.value = false
  }
}

async function publishPermission(permissionId: string) {
  await access.publishProjectPermission(projectId.value, permissionId)
  toast.add({ title: 'Permission published to access catalog', color: 'success' })
  await catalogRequest?.refresh()
  await refreshProjectAndAudit()
}

async function publishRole(roleId: string) {
  await access.publishProjectRole(projectId.value, roleId)
  toast.add({ title: 'Role published to access catalog', color: 'success' })
  await catalogRequest?.refresh()
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
  let registrationRoleId: string | null = null
  if (registration.mode === 'public' && registration.roleId?.startsWith('catalog:')) {
    const catalogRoleId = registration.roleId.slice('catalog:'.length)
    await access.importCatalogItems(projectId.value, { permission_ids: [], role_ids: [catalogRoleId] })
    await refresh()
    const catalogRole = catalog.value?.roles.find(item => item.id === catalogRoleId)
    registrationRoleId = project.value?.roles.find(item => item.catalog_role_id === catalogRoleId || item.slug === catalogRole?.slug)?.id ?? null
  } else if (registration.mode === 'public' && registration.roleId?.startsWith('project:')) {
    registrationRoleId = registration.roleId.slice('project:'.length)
  }
  await access.updateProjectRegistration(projectId.value, {
    registration_mode: registration.mode,
    registration_role_id: registrationRoleId,
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

async function scheduleDeletion() {
  if (!project.value) return
  deletionSaving.value = true
  try {
    await access.scheduleProjectDeletion(projectId.value, deletionConfirmation.value)
    deletionModalOpen.value = false
    deletionConfirmation.value = ''
    toast.add({ title: 'Project deletion scheduled', description: 'Access was revoked immediately. You can cancel before the purge deadline.', color: 'warning' })
    await refreshProjectAndAudit()
  } finally {
    deletionSaving.value = false
  }
}

async function cancelDeletion() {
  if (!project.value) return
  deletionSaving.value = true
  try {
    await access.cancelProjectDeletion(projectId.value)
    toast.add({ title: 'Project deletion cancelled', color: 'success' })
    await refreshProjectAndAudit()
  } finally {
    deletionSaving.value = false
  }
}

async function suspendProject() {
  if (!project.value) return
  suspensionSaving.value = true
  try {
    await access.suspendProject(projectId.value, suspensionConfirmation.value)
    suspensionModalOpen.value = false
    suspensionConfirmation.value = ''
    toast.add({ title: 'Project suspended', description: 'All active project sessions were revoked immediately.', color: 'warning' })
    await refreshProjectAndAudit()
  } finally {
    suspensionSaving.value = false
  }
}

async function reactivateProject() {
  if (!project.value) return
  suspensionSaving.value = true
  try {
    await access.reactivateProject(projectId.value)
    toast.add({ title: 'Project reactivated', description: 'Users can authenticate again with new sessions.', color: 'success' })
    await refreshProjectAndAudit()
  } finally {
    suspensionSaving.value = false
  }
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
  const catalogPermissionIds = selectedRole.value.permissionIds
    .filter(item => item.startsWith('catalog:'))
    .map(item => item.slice('catalog:'.length))
  if (catalogPermissionIds.length > 0) {
    await access.importCatalogItems(projectId.value, { permission_ids: catalogPermissionIds, role_ids: [] })
    await refresh()
  }
  const permissionIds = selectedRole.value.permissionIds.flatMap((item) => {
    if (item.startsWith('project:')) return [item.slice('project:'.length)]
    const catalogPermissionId = item.slice('catalog:'.length)
    const catalogPermission = catalog.value?.permissions.find(permission => permission.id === catalogPermissionId)
    const projectPermission = project.value?.permissions.find(permission => (
      permission.catalog_permission_id === catalogPermissionId || permission.key === catalogPermission?.key
    ))
    return projectPermission ? [projectPermission.id] : []
  })
  await access.setRolePermissions(projectId.value, selectedRole.value.id, permissionIds)
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
  const permissionIds = item.permission_ids.map((permissionId) => {
    const permission = project.value?.permissions.find(candidate => candidate.id === permissionId)
    return permission?.catalog_origin === 'imported' && permission.catalog_permission_id
      ? `catalog:${permission.catalog_permission_id}`
      : `project:${permissionId}`
  })
  selectedRole.value = { id: item.id, name: item.name, permissionIds }
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
      <UAlert
        v-if="projectChangesLocked"
        color="warning"
        variant="soft"
        icon="i-lucide-lock-keyhole"
        title="Project changes are locked"
        :description="projectChangesLockMessage"
      />

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
        <UAlert
          v-if="project.status === 'pending_deletion'"
          color="warning"
          variant="soft"
          icon="i-lucide-triangle-alert"
          title="Project deletion is scheduled"
          :description="`Identity access is disabled. This project will be permanently purged after ${new Date(project.deletion_scheduled_at || '').toLocaleString()}.`"
        />
        <UAlert
          v-else-if="project.status === 'suspended'"
          color="warning"
          variant="soft"
          icon="i-lucide-pause-circle"
          title="Project is suspended"
          description="Identity access is disabled and active project sessions have been revoked. Reactivate the project to resume access and configuration changes."
        />
        <div class="grid items-start gap-5 lg:grid-cols-2">
          <UPageCard
            title="Project environment"
            description="Control runtime mode and sandbox lifetime directly."
            variant="subtle"
            class="self-start"
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
                  :disabled="projectChangesLocked"
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
                  :disabled="projectChangesLocked"
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
                :disabled="projectChangesLocked"
              />
            </form>
          </UPageCard>

          <UPageCard
            title="Registration policy"
            description="Control how identities become members of this project."
            variant="subtle"
            class="self-start"
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
                  :disabled="projectChangesLocked"
                />
              </UFormField>

              <UFormField
                v-if="registration.mode === 'public'"
                label="Default role"
                description="Optional role assigned to every new member."
              >
                <USelect
                  v-model="registration.roleId"
                  :items="registrationRoleOptions"
                  class="w-full"
                  :disabled="projectChangesLocked"
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
                  :disabled="projectChangesLocked"
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
                :disabled="projectChangesLocked"
              />
            </form>
          </UPageCard>
        </div>

        <UPageCard
          v-if="canManageDeletion"
          title="Danger zone"
          description="Suspend access immediately or schedule permanent deletion. Audit records are retained after the purge."
          variant="subtle"
          class="border border-error/30"
        >
          <div class="space-y-4">
            <div
              v-if="project.status !== 'pending_deletion'"
              class="flex flex-wrap items-center justify-between gap-4"
            >
              <p class="text-sm text-muted">
                {{ project.status === 'suspended' ? 'Identity access is paused. Reactivation does not restore prior sessions.' : 'Suspension revokes every active project session immediately.' }}
              </p>
              <UButton
                v-if="project.status === 'suspended'"
                label="Reactivate project"
                icon="i-lucide-play-circle"
                color="success"
                variant="outline"
                :loading="suspensionSaving"
                @click="reactivateProject"
              />
              <UButton
                v-else
                label="Suspend project"
                icon="i-lucide-pause-circle"
                color="warning"
                variant="outline"
                @click="() => { suspensionModalOpen = true }"
              />
            </div>
            <div class="flex flex-wrap items-center justify-between gap-4 border-t border-default pt-4">
              <p class="text-sm text-muted">
                {{ project.status === 'pending_deletion' ? 'The project can still be restored before its purge deadline.' : 'Permanent removal is delayed for the configured recovery period.' }}
              </p>
              <UButton
                v-if="project.status === 'pending_deletion'"
                label="Cancel deletion"
                icon="i-lucide-rotate-ccw"
                color="warning"
                :loading="deletionSaving"
                @click="cancelDeletion"
              />
              <UButton
                v-else
                label="Schedule deletion"
                icon="i-lucide-trash-2"
                color="error"
                variant="outline"
                @click="() => { deletionModalOpen = true }"
              />
            </div>
          </div>
        </UPageCard>
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
              :disabled="projectChangesLocked"
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
                  :disabled="projectChangesLocked"
                  @click="selectMembership(row.original)"
                />
              </div>
            </template>
          </UTable>
        </IdentityTableCard>
      </template>

      <template v-else-if="activeTab === 'access'">
        <div class="space-y-5">
          <IdentityTableCard
            title="Roles"
            description="Catalog roles are reusable across projects. Project roles can be configured locally."
            :count="roleRows.length"
          >
            <template #actions>
              <UButton
                label="New role"
                icon="i-lucide-plus"
                size="sm"
                :disabled="projectChangesLocked"
                @click="() => { roleModalOpen = true }"
              />
            </template>
            <UTable
              :data="roleRows"
              :columns="roleColumns"
              empty="No roles are available yet."
            >
              <template #name-cell="{ row }">
                <div class="py-1">
                  <div class="flex items-center gap-2">
                    <p class="font-medium text-highlighted">
                      {{ row.original.name }}
                    </p>
                    <UBadge
                      v-if="row.original.global"
                      color="primary"
                      variant="soft"
                    >
                      {{ row.original.availableCatalog ? 'Available catalog' : 'Catalog' }}
                    </UBadge>
                  </div>
                  <p class="text-xs text-muted">
                    {{ row.original.slug }}
                  </p>
                </div>
              </template>
              <template #permissionCount-cell="{ row }">
                <span class="text-sm text-muted">{{ row.original.permissionCount }} assigned</span>
              </template>
              <template #actions-cell="{ row }">
                <div
                  v-if="row.original.projectRole"
                  class="flex justify-end gap-1"
                >
                  <UButton
                    label="Configure"
                    icon="i-lucide-sliders-horizontal"
                    color="neutral"
                    variant="ghost"
                    :disabled="projectChangesLocked"
                    @click="selectRole(row.original.projectRole)"
                  />
                  <UButton
                    v-if="canManageDeletion && !row.original.projectRole.catalog_role_id"
                    label="Publish"
                    icon="i-lucide-upload"
                    color="neutral"
                    variant="ghost"
                    :disabled="projectChangesLocked"
                    @click="publishRole(row.original.projectRole.id)"
                  />
                  <UButton
                    label="Delete"
                    icon="i-lucide-trash-2"
                    color="error"
                    variant="ghost"
                    :disabled="projectChangesLocked"
                    @click="openAccessDeletion({ kind: 'role', resource: row.original.projectRole })"
                  />
                </div>
              </template>
            </UTable>
          </IdentityTableCard>

          <IdentityTableCard
            title="Project permissions"
            description="Permissions created by this project. Publishing adds one to the reusable access catalog."
            :count="projectPermissions.length"
          >
            <template #actions>
              <UButton
                label="New permission"
                icon="i-lucide-plus"
                size="sm"
                :disabled="projectChangesLocked"
                @click="() => { permissionModalOpen = true }"
              />
            </template>
            <UTable
              :data="projectPermissions"
              :columns="permissionColumns"
              empty="No project permissions yet."
              class="min-w-2xl"
            >
              <template #key-cell="{ row }">
                <div class="flex items-center gap-2">
                  <code class="rounded-md bg-elevated px-2 py-1 text-xs">{{ row.original.key }}</code>
                  <UBadge
                    v-if="row.original.catalog_permission_id"
                    color="primary"
                    variant="soft"
                  >
                    Catalog
                  </UBadge>
                </div>
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
              <template #actions-cell="{ row }">
                <div class="flex justify-end gap-1">
                  <UButton
                    v-if="canManageDeletion && !row.original.catalog_permission_id"
                    label="Publish"
                    icon="i-lucide-upload"
                    color="neutral"
                    variant="ghost"
                    :disabled="projectChangesLocked"
                    @click="publishPermission(row.original.id)"
                  />
                  <UButton
                    label="Delete"
                    icon="i-lucide-trash-2"
                    color="error"
                    variant="ghost"
                    :disabled="projectChangesLocked"
                    @click="openAccessDeletion({ kind: 'permission', resource: row.original })"
                  />
                </div>
              </template>
            </UTable>
          </IdentityTableCard>
        </div>
      </template>

      <template v-else-if="activeTab === 'clients'">
        <UAlert
          v-if="projectChangesLocked"
          color="warning"
          variant="soft"
          icon="i-lucide-lock-keyhole"
          title="Client management is locked"
          :description="projectChangesLockMessage"
        />
        <IdentityTableCard
          title="Confidential clients"
          description="Create one credential per BFF, API, worker, or environment."
          :count="project.clients.length"
        >
          <template #actions>
            <UButton
              label="New client"
              icon="i-lucide-plus"
              :disabled="projectChangesLocked"
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
                  :disabled="projectChangesLocked"
                  @click="toggleClient(row.original)"
                />
                <UButton
                  label="Rotate"
                  icon="i-lucide-refresh-cw"
                  color="neutral"
                  variant="outline"
                  :disabled="projectChangesLocked"
                  @click="rotateClient(row.original.id)"
                />
                <UTooltip :text="projectChangesLocked ? projectChangesLockMessage : (clientDependencies(row.original).length ? `Used by: ${clientDependencies(row.original).join(', ')}` : 'Delete client')">
                  <UButton
                    label="Delete"
                    icon="i-lucide-trash-2"
                    color="error"
                    variant="ghost"
                    :disabled="projectChangesLocked || clientDependencies(row.original).length > 0"
                    @click="openClientDeletion(row.original)"
                  />
                </UTooltip>
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
              :disabled="projectChangesLocked"
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
                      :disabled="projectChangesLocked"
                      @click="openHostedApplication(application)"
                    />
                    <UButton
                      icon="i-lucide-trash-2"
                      color="error"
                      variant="ghost"
                      aria-label="Delete hosted application"
                      :disabled="projectChangesLocked"
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
              :disabled="projectChangesLocked"
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
                  :disabled="projectChangesLocked"
                  @click="toggleWebhook(row.original)"
                />
                <UButton
                  icon="i-lucide-refresh-cw"
                  color="neutral"
                  variant="ghost"
                  aria-label="Rotate webhook secret"
                  :disabled="projectChangesLocked"
                  @click="rotateWebhook(row.original.id)"
                />
                <UButton
                  icon="i-lucide-trash-2"
                  color="error"
                  variant="ghost"
                  aria-label="Delete webhook"
                  :disabled="projectChangesLocked"
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
        v-model:open="clientDeletionModalOpen"
        title="Delete confidential client"
        :description="selectedClientForDeletion ? `Type ${selectedClientForDeletion.name} to permanently delete this client and revoke its credentials.` : ''"
        @update:open="value => { if (!value) selectedClientForDeletion = null }"
      >
        <template #body>
          <form
            v-if="selectedClientForDeletion"
            class="space-y-5"
            @submit.prevent="deleteClient"
          >
            <UAlert
              color="warning"
              variant="soft"
              icon="i-lucide-triangle-alert"
              title="This cannot be undone"
              description="Active sessions and refresh tokens issued by this client will be revoked. Project roles and permissions remain available to other clients."
            />
            <UFormField
              label="Client name"
              :description="`Type ${selectedClientForDeletion.name} exactly to continue.`"
            >
              <UInput
                v-model="clientDeletionConfirmation"
                autofocus
                class="w-full"
              />
            </UFormField>
            <p
              v-if="selectedClientDependencies.length"
              class="text-sm text-error"
            >
              Reassign or remove these hosted applications first: {{ selectedClientDependencies.map(application => application.name).join(', ') }}.
            </p>
            <div class="flex justify-end gap-2">
              <UButton
                label="Cancel"
                color="neutral"
                variant="ghost"
                @click="() => { clientDeletionModalOpen = false }"
              />
              <UButton
                type="submit"
                label="Delete client"
                color="error"
                icon="i-lucide-trash-2"
                :disabled="clientDeletionConfirmation !== selectedClientForDeletion.name || selectedClientDependencies.length > 0"
                :loading="clientDeletionSaving"
              />
            </div>
          </form>
        </template>
      </UModal>
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
        v-model:open="deletionModalOpen"
        title="Schedule project deletion"
        :description="project ? `Type ${project.slug} to confirm. Access is revoked immediately and deletion remains reversible until the configured deadline.` : ''"
      >
        <template #body>
          <form
            v-if="project"
            class="space-y-5"
            @submit.prevent="scheduleDeletion"
          >
            <UFormField
              label="Project slug"
              :description="`Type ${project.slug} exactly to continue.`"
            >
              <UInput
                v-model="deletionConfirmation"
                autofocus
                class="w-full"
              />
            </UFormField>
            <div class="flex justify-end gap-2">
              <UButton
                label="Cancel"
                color="neutral"
                variant="ghost"
                @click="() => { deletionModalOpen = false }"
              />
              <UButton
                type="submit"
                label="Schedule deletion"
                color="error"
                icon="i-lucide-trash-2"
                :disabled="deletionConfirmation !== project.slug"
                :loading="deletionSaving"
              />
            </div>
          </form>
        </template>
      </UModal>
      <UModal
        v-model:open="suspensionModalOpen"
        title="Suspend project"
        :description="project ? `Type ${project.slug} to confirm. This revokes every active project session immediately; reactivation requires users to sign in again.` : ''"
      >
        <template #body>
          <form
            v-if="project"
            class="space-y-5"
            @submit.prevent="suspendProject"
          >
            <UFormField
              label="Project slug"
              :description="`Type ${project.slug} exactly to continue.`"
            >
              <UInput
                v-model="suspensionConfirmation"
                autofocus
                class="w-full"
              />
            </UFormField>
            <div class="flex justify-end gap-2">
              <UButton
                label="Cancel"
                color="neutral"
                variant="ghost"
                @click="() => { suspensionModalOpen = false }"
              />
              <UButton
                type="submit"
                label="Suspend project"
                color="warning"
                icon="i-lucide-pause-circle"
                :disabled="suspensionConfirmation !== project.slug"
                :loading="suspensionSaving"
              />
            </div>
          </form>
        </template>
      </UModal>
      <UModal
        :open="selectedAccessDeletion != null"
        :title="selectedAccessDeletion?.kind === 'role' ? 'Delete project role' : 'Delete project permission'"
        :description="selectedAccessDeletion ? `Type ${accessDeletionExpectedConfirmation} exactly to permanently delete this ${selectedAccessDeletion.kind}.` : ''"
        @update:open="value => { if (!value) selectedAccessDeletion = null }"
      >
        <template #body>
          <form
            v-if="selectedAccessDeletion"
            class="space-y-5"
            @submit.prevent="deleteSelectedAccessResource"
          >
            <UAlert
              v-if="accessDeletionBlocked"
              color="warning"
              variant="soft"
              icon="i-lucide-link-2"
              title="Resolve dependencies before deleting"
              description="Access assignments are never removed automatically. Reassign or remove the items below, then retry."
            />
            <UAlert
              v-else
              color="error"
              variant="soft"
              icon="i-lucide-triangle-alert"
              title="This cannot be undone"
              :description="selectedAccessDeletion.kind === 'role' ? 'The role and its permission composition will be permanently removed.' : 'The permission will be permanently removed from this project. Reusable catalog entries are not affected.'"
            />
            <div
              v-if="accessDeletionBlocked"
              class="space-y-3 rounded-xl border border-default p-4 text-sm"
            >
              <div v-if="accessDeletionMemberships.length">
                <p class="font-medium text-highlighted">
                  Assigned memberships
                </p>
                <ul class="mt-1 list-disc space-y-1 pl-5 text-muted">
                  <li
                    v-for="membership in accessDeletionMemberships"
                    :key="membership.id"
                  >
                    {{ membership.user.username || membership.user.email || membership.user.id }} ({{ membership.status }})
                  </li>
                </ul>
                <UButton
                  class="mt-2"
                  label="Manage members"
                  size="xs"
                  color="neutral"
                  variant="soft"
                  @click="() => { selectedAccessDeletion = null; activeTab = 'members' }"
                />
              </div>
              <div v-if="accessDeletionRoles.length">
                <p class="font-medium text-highlighted">
                  Granted to roles
                </p>
                <p class="mt-1 text-muted">
                  {{ accessDeletionRoles.map(role => `${role.name} (${role.slug})`).join(', ') }}
                </p>
              </div>
              <div v-if="accessDeletionIsRegistrationDefault">
                <p class="font-medium text-highlighted">
                  Public registration default
                </p>
                <p class="mt-1 text-muted">
                  Select another default role or disable the default role in the registration policy.
                </p>
              </div>
              <div v-if="accessDeletionManifestClient">
                <p class="font-medium text-highlighted">
                  Managed by {{ accessDeletionManifestClient.name }}
                </p>
                <p class="mt-1 text-muted">
                  Remove this key from the client permission manifest first. It can be deleted after the next synchronization marks it stale.
                </p>
              </div>
            </div>
            <UAlert
              v-if="accessDeletionServerMessage"
              color="warning"
              variant="soft"
              title="Dependencies changed"
              :description="accessDeletionServerMessage"
            />
            <UFormField
              :label="selectedAccessDeletion.kind === 'role' ? 'Role slug' : 'Permission key'"
              :description="`Type ${accessDeletionExpectedConfirmation} exactly to continue.`"
            >
              <UInput
                v-model="accessDeletionConfirmation"
                autofocus
                class="w-full"
              />
            </UFormField>
            <div class="flex justify-end gap-2">
              <UButton
                label="Cancel"
                color="neutral"
                variant="ghost"
                @click="() => { selectedAccessDeletion = null }"
              />
              <UButton
                type="submit"
                :label="selectedAccessDeletion.kind === 'role' ? 'Delete role' : 'Delete permission'"
                color="error"
                icon="i-lucide-trash-2"
                :disabled="accessDeletionBlocked || accessDeletionConfirmation !== accessDeletionExpectedConfirmation"
                :loading="accessDeletionSaving"
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
            <div class="max-h-96 space-y-2 overflow-y-auto rounded-xl border border-default p-3">
              <label
                v-for="permission in rolePermissionChoices"
                :key="permission.value"
                class="flex cursor-pointer items-center justify-between gap-3 rounded-lg px-3 py-2 hover:bg-elevated"
              >
                <UCheckbox
                  v-model="selectedRole.permissionIds"
                  :value="permission.value"
                  :label="permission.label"
                />
                <UBadge
                  v-if="permission.global"
                  color="primary"
                  variant="soft"
                >
                  Catalog
                </UBadge>
              </label>
              <p
                v-if="rolePermissionChoices.length === 0"
                class="py-5 text-center text-sm text-muted"
              >
                No permissions are available yet.
              </p>
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
