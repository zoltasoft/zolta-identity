import type { IdentityBrowserSession } from './identity-auth'

declare module '#auth-utils' {
  interface User {
    id: string
    name: string
    email: string
    username: string
    emailVerified: boolean
    isTemporary?: boolean
    expiresAt?: string | null
  }

  interface UserSession {
    lastLoggedIn: Date
    identity?: IdentityBrowserSession
  }

  interface SecureSessionData {
    identityAccessToken?: string | null
    identityAccessTokenExpiresAt?: string | null
    identityRefreshToken?: string | null
    identityRefreshTokenExpiresAt?: string | null
    identityConnection?: 'primary' | 'sandbox'
  }
}

export {}
