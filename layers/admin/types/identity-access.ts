export type IdentityProject = {
  id: string
  name: string
  slug: string
  description: string | null
  status: 'active' | 'suspended'
  mode: 'live' | 'sandbox'
  sandbox_ttl_minutes: number
  registration_mode: 'invite_only' | 'public'
  registration_role_id: string | null
  email_verification_required: boolean
}

export type IdentityClient = {
  id: string
  project_id: string
  name: string
  secret_prefix: string
  status: 'active' | 'disabled'
  last_used_at: string | null
  client_secret?: string
}

export type IdentityPermission = {
  id: string
  project_id: string
  key: string
  name: string
  description: string | null
  source: 'manual' | 'manifest'
  source_client_id: string | null
  status: 'active' | 'stale'
}

export type IdentityRole = {
  id: string
  project_id: string
  name: string
  slug: string
  description: string | null
  permission_ids: string[]
}

export type IdentityWebhook = {
  id: string
  project_id: string
  url: string
  events: Array<'identity.user.expired' | 'identity.user.deletion_requested'>
  secret_prefix: string
  status: 'active' | 'disabled'
  last_delivered_at: string | null
  secret?: string
}

export type IdentityHostedApplication = {
  id: string
  project_id: string
  primary_client_id: string
  sandbox_client_id: string | null
  key: string
  name: string
  application_url: string
  callback_url: string
  appearance: {
    welcome_text: string | null
    accent_color: string | null
    background_preset: 'identity' | 'slate' | 'indigo' | 'emerald' | 'sunset'
    logo_url: string | null
  }
  status: 'active' | 'disabled'
}

export type IdentityMembership = {
  id: string
  project_id: string
  user: { id: string, email?: string, username?: string }
  status: 'active' | 'suspended'
  is_admin: boolean
  authorization_version: number
  role_ids: string[]
  direct_permission_ids: string[]
  roles: string[]
  permissions: string[]
}

export type IdentityProjectDetails = IdentityProject & {
  clients: IdentityClient[]
  memberships: IdentityMembership[]
  roles: IdentityRole[]
  permissions: IdentityPermission[]
  webhooks: IdentityWebhook[]
  hosted_applications: IdentityHostedApplication[]
}

export type {
  IdentityBrowserSession,
  IdentityLoginData
} from '../../auth/shared/types/identity-auth'

export type IdentityAccountSession = {
  id: string
  current: boolean
  project: IdentityProject | null
  client: IdentityClient | null
  created_at: string | null
  expires_at: string
}

export type IdentityAuditEvent = {
  id: string
  event: string
  actor_user_id: string | null
  client_id: string | null
  target_type: string | null
  target_id: string | null
  metadata: Record<string, unknown>
  ip_address: string | null
  created_at: string | null
}

export type IdentityInstallationUser = {
  id: string
  email: string
  username: string
  email_verified_at: string | null
  is_system_admin: boolean
  locked: boolean
  project_count: number
  created_at: string | null
}

export type IdentityGlobalPermission = {
  id: string
  name: string
  description: string | null
  roles: string[]
  users: string[]
  created_at: string
  updated_at: string
}

export type IdentityGlobalRole = {
  id: string
  name: string
  description: string | null
  permissions: IdentityGlobalPermission[]
  users: string[]
  created_at: string
  updated_at: string
}
