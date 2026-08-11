import type {
  IdentityBrowserSession,
  IdentityAccountSession,
  IdentityAuditEvent,
  IdentityClient,
  IdentityHostedApplication,
  IdentityInstallationUser,
  IdentityGlobalPermission,
  IdentityGlobalRole,
  IdentityProject,
  IdentityProjectDetails,
  IdentityRole,
  IdentityWebhook
} from '../../types/identity-access'

export function useIdentityAccess() {
  const authenticatedFetch = useAuthenticatedFetch()
  const { csrf, headerName } = useCsrf()
  const mutation = <T>(url: string, options: Parameters<typeof authenticatedFetch<T>>[1]) =>
    authenticatedFetch<T>(url, {
      ...options,
      headers: { [headerName]: csrf, ...options?.headers }
    })

  return {
    session: () => useFetch<IdentityBrowserSession>('/api/auth/session'),
    projects: () => useFetch<IdentityProject[]>('/api/identity/projects'),
    users: () => useFetch<IdentityInstallationUser[]>('/api/identity/users'),
    globalRoles: () => useFetch<IdentityGlobalRole[]>('/api/identity/global/roles'),
    globalPermissions: () => useFetch<IdentityGlobalPermission[]>('/api/identity/global/permissions'),
    accountSessions: () => useFetch<IdentityAccountSession[]>('/api/auth/sessions'),
    project: (id: MaybeRefOrGetter<string>) => useFetch<IdentityProjectDetails>(() => `/api/identity/projects/${toValue(id)}`),
    audit: (id: MaybeRefOrGetter<string>, options: { immediate?: boolean } = {}) => useFetch<IdentityAuditEvent[]>(
      () => `/api/identity/projects/${toValue(id)}/audit`,
      { immediate: options.immediate ?? true }
    ),
    createProject: (body: { name: string, slug: string, description?: string | null }) =>
      mutation<IdentityProject>('/api/identity/projects', { method: 'POST', body }),
    updateProjectRegistration: (
      projectId: string,
      body: { registration_mode: 'invite_only' | 'public', registration_role_id: string | null, email_verification_required: boolean }
    ) => mutation(`/api/identity/projects/${projectId}/registration`, { method: 'PATCH', body }),
    updateProjectEnvironment: (
      projectId: string,
      body: { mode: 'live' | 'sandbox', sandbox_ttl_minutes: number }
    ) => mutation(`/api/identity/projects/${projectId}/environment`, { method: 'PATCH', body }),
    createWebhook: (projectId: string, body: { url: string, events: string[] }) =>
      mutation<IdentityWebhook>(`/api/identity/projects/${projectId}/webhooks`, { method: 'POST', body }),
    updateWebhook: (projectId: string, webhookId: string, body: { url: string, events: string[], status: 'active' | 'disabled' }) =>
      mutation(`/api/identity/projects/${projectId}/webhooks/${webhookId}`, { method: 'PUT', body }),
    rotateWebhookSecret: (projectId: string, webhookId: string) =>
      mutation<IdentityWebhook>(`/api/identity/projects/${projectId}/webhooks/${webhookId}/rotate-secret`, { method: 'POST' }),
    removeWebhook: (projectId: string, webhookId: string) =>
      mutation(`/api/identity/projects/${projectId}/webhooks/${webhookId}`, { method: 'DELETE' }),
    updateUser: (userId: string, body: { is_system_admin: boolean, locked: boolean }) =>
      mutation(`/api/identity/users/${userId}`, { method: 'PATCH', body }),
    createGlobalRole: (body: { name: string, description?: string | null, permission_ids: string[] }) =>
      mutation<IdentityGlobalRole>('/api/identity/global/roles', { method: 'POST', body }),
    deleteGlobalRole: (roleId: string) =>
      mutation(`/api/identity/global/roles/${roleId}`, { method: 'DELETE' }),
    createGlobalPermission: (body: { name: string, description?: string | null }) =>
      mutation<IdentityGlobalPermission>('/api/identity/global/permissions', { method: 'POST', body }),
    deleteGlobalPermission: (permissionId: string) =>
      mutation(`/api/identity/global/permissions/${permissionId}`, { method: 'DELETE' }),
    updateAccount: (body: { username: string, email: string, avatar_url: string | null }) =>
      mutation<Record<string, unknown>>('/api/identity/account', { method: 'PATCH', body }),
    updateAccountSecurity: (body: { two_factor_enabled: boolean, login_alerts_enabled: boolean, backup_email: string | null }) =>
      mutation<Record<string, unknown>>('/api/identity/account/security', { method: 'PATCH', body }),
    changePassword: (body: { current_password: string, password: string, password_confirmation: string }) =>
      mutation<{ message: string }>('/api/identity/account/password', { method: 'PATCH', body }),
    revokeAccountSession: (sessionId: string) =>
      mutation(`/api/auth/sessions/${sessionId}`, { method: 'DELETE' }),
    createClient: (projectId: string, name: string) =>
      mutation<IdentityClient>(`/api/identity/projects/${projectId}/clients`, { method: 'POST', body: { name } }),
    createHostedApplication: (
      projectId: string,
      body: { name: string, key: string, primary_client_id: string, sandbox_client_id: string | null, application_url: string, callback_url: string, appearance: IdentityHostedApplication['appearance'] }
    ) => mutation<IdentityHostedApplication>(`/api/identity/projects/${projectId}/hosted-applications`, { method: 'POST', body }),
    updateHostedApplication: (
      projectId: string,
      applicationId: string,
      body: { name: string, primary_client_id: string, sandbox_client_id: string | null, application_url: string, callback_url: string, status: 'active' | 'disabled', appearance: IdentityHostedApplication['appearance'] }
    ) => mutation(`/api/identity/projects/${projectId}/hosted-applications/${applicationId}`, { method: 'PATCH', body }),
    uploadHostedApplicationLogo: (projectId: string, applicationId: string, logo: File) => {
      const formData = new FormData()
      formData.set('logo', logo)
      return mutation<IdentityHostedApplication>(`/api/identity/projects/${projectId}/hosted-applications/${applicationId}/logo`, { method: 'POST', body: formData })
    },
    removeHostedApplicationLogo: (projectId: string, applicationId: string) =>
      mutation(`/api/identity/projects/${projectId}/hosted-applications/${applicationId}/logo`, { method: 'DELETE' }),
    removeHostedApplication: (projectId: string, applicationId: string) =>
      mutation(`/api/identity/projects/${projectId}/hosted-applications/${applicationId}`, { method: 'DELETE' }),
    rotateClientSecret: (projectId: string, clientId: string) =>
      mutation<IdentityClient>(`/api/identity/projects/${projectId}/clients/${clientId}/rotate-secret`, { method: 'POST' }),
    setClientStatus: (projectId: string, clientId: string, status: 'active' | 'disabled') =>
      mutation(`/api/identity/projects/${projectId}/clients/${clientId}`, { method: 'PATCH', body: { status } }),
    createRole: (projectId: string, body: { name: string, slug: string, description?: string | null }) =>
      mutation<IdentityRole>(`/api/identity/projects/${projectId}/roles`, { method: 'POST', body }),
    createPermission: (projectId: string, body: { key: string, name?: string | null, description?: string | null }) =>
      mutation(`/api/identity/projects/${projectId}/permissions`, { method: 'POST', body }),
    syncPermissionManifest: (
      projectId: string,
      clientId: string,
      permissions: Array<{ key: string, name?: string, description?: string }>
    ) => mutation(`/api/identity/projects/${projectId}/clients/${clientId}/permission-manifest`, {
      method: 'PUT',
      body: { permissions }
    }),
    setRolePermissions: (projectId: string, roleId: string, permissionIds: string[]) =>
      mutation(`/api/identity/projects/${projectId}/roles/${roleId}/permissions`, {
        method: 'PUT',
        body: { permission_ids: permissionIds }
      }),
    setMembershipAccess: (
      projectId: string,
      membershipId: string,
      body: { role_ids: string[], permission_ids: string[], is_admin: boolean, status: 'active' | 'suspended' }
    ) => mutation(`/api/identity/projects/${projectId}/memberships/${membershipId}/access`, { method: 'PUT', body }),
    removeMembership: (projectId: string, membershipId: string) =>
      mutation(`/api/identity/projects/${projectId}/memberships/${membershipId}`, { method: 'DELETE' }),
    invite: (projectId: string, body: { email: string, is_admin: boolean }) =>
      mutation<Record<string, unknown>>(`/api/identity/projects/${projectId}/invitations`, { method: 'POST', body }),
    logout: () => mutation<{ success: boolean }>('/api/auth/logout', { method: 'POST' })
  }
}
