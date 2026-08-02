export type IdentityBrowserSession = {
  projectId: string
  projectName: string
  projectSlug: string
  clientId: string
  membershipId: string
  isProjectAdmin: boolean
  isSystemAdmin: boolean
  roles: string[]
  permissions: string[]
  authorizationVersion: number
  accessTokenExpiresAt: string
}

export type IdentityLoginData = {
  access_token: string
  access_token_expires_at: string
  refresh_token: string
  refresh_token_expires_at: string
  identity: {
    user: {
      id: string
      email: string
      username: string
      avatar_url: string | null
      email_verified: boolean
      is_system_admin: boolean
    }
    project: {
      id: string
      name: string
      slug: string
      registration_mode: 'invite_only' | 'public'
      registration_role_id: string | null
    }
    client: { id: string }
    membership: {
      id: string
      is_admin: boolean
      roles: string[]
      permissions: string[]
      authorization_version: number
    }
  }
}
