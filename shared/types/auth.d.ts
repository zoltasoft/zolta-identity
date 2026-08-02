// shared/types/auth.d.ts
declare module '#auth-utils' {
  interface User {
    id: string
    name: string
    email: string
    username: string
  }

  interface UserSession {
    lastLoggedIn: Date
    identity?: {
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
  }

  interface SecureSessionData {
    identityAccessToken: string | null
    identityAccessTokenExpiresAt: string | null
    identityRefreshToken: string | null
    identityRefreshTokenExpiresAt: string | null
  }
}

export {}
