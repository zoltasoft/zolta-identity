export function createAuthRuntimeConfig(
  env: NodeJS.ProcessEnv = process.env
) {
  return {
    session: {
      password: env.NUXT_SESSION_PASSWORD,
      name: 'identity-console-session',
      cookie: {
        httpOnly: true,
        sameSite: 'lax',
        secure: env.NODE_ENV === 'production',
        path: '/',
        maxAge: 60 * 60 * 24 * 7
      }
    }
  }
}

export function createAuthCsurfConfig(
  env: NodeJS.ProcessEnv = process.env
) {
  return {
    headerName: 'csrf-token',
    cookieKey: 'identity-console-csrf',
    cookie: {
      path: '/',
      httpOnly: true,
      sameSite: 'lax' as const,
      secure: env.NODE_ENV === 'production'
    }
  }
}
