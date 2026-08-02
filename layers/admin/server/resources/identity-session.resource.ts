import type {
  IdentityBrowserSession,
  IdentityLoginData
} from '../../types/identity-access'

export function toIdentityBrowserSession(data: IdentityLoginData): IdentityBrowserSession {
  return {
    projectId: data.identity.project.id,
    projectName: data.identity.project.name,
    projectSlug: data.identity.project.slug,
    clientId: data.identity.client.id,
    membershipId: data.identity.membership.id,
    isProjectAdmin: data.identity.membership.is_admin,
    isSystemAdmin: data.identity.user.is_system_admin,
    roles: data.identity.membership.roles,
    permissions: data.identity.membership.permissions,
    authorizationVersion: data.identity.membership.authorization_version,
    accessTokenExpiresAt: data.access_token_expires_at
  }
}
