import type {
  IdentityAccountSession,
  IdentityAuthenticationExperience,
  IdentityBrowserSession,
  IdentityConnectionName,
  IdentityLoginInput,
  IdentityRegisterInput,
  IdentityResetPasswordInput
} from '../../shared/types/identity-auth'
import { useIdentityMutation } from './useIdentityMutation'

export function useIdentityAuth() {
  const userSession = useUserSession()
  const mutation = useIdentityMutation()

  const login = async (input: IdentityLoginInput) => {
    const identity = await mutation<IdentityBrowserSession>('/api/auth/login', {
      method: 'POST',
      body: input
    })
    await userSession.fetch()
    return identity
  }

  const register = async (input: IdentityRegisterInput) => {
    const identity = await mutation<IdentityBrowserSession>('/api/auth/register', {
      method: 'POST',
      body: input
    })
    await userSession.fetch()
    return identity
  }

  const logout = async () => {
    await mutation<{ success: boolean }>('/api/auth/logout', {
      method: 'POST'
    })
    await userSession.clear()
  }

  const createSandboxSession = async (connection: IdentityConnectionName = 'sandbox') => {
    const identity = await mutation<IdentityBrowserSession>('/api/auth/sandbox-session', {
      method: 'POST',
      body: { connection }
    })
    await userSession.fetch()
    return identity
  }

  return {
    ...userSession,
    login,
    register,
    createSandboxSession,
    authenticationExperience: () => $fetch<IdentityAuthenticationExperience>('/api/auth/context'),
    logout,
    forgotPassword: (email: string) =>
      mutation<Record<string, unknown>>('/api/auth/password/forgot', {
        method: 'POST',
        body: { email }
      }),
    resetPassword: (input: IdentityResetPasswordInput) =>
      mutation<Record<string, unknown>>('/api/auth/password/reset', {
        method: 'POST',
        body: input
      }),
    resendVerification: () =>
      mutation<Record<string, unknown>>('/api/auth/email/resend', {
        method: 'POST'
      }),
    verifyEmail: (code: string) =>
      mutation<Record<string, unknown>>('/api/auth/email/verification', {
        method: 'POST',
        body: { code }
      }),
    sessions: () => $fetch<IdentityAccountSession[]>('/api/auth/sessions'),
    revokeSession: (id: string) =>
      mutation<Record<string, unknown>>(`/api/auth/sessions/${id}`, {
        method: 'DELETE'
      })
  }
}
