import type { H3Event } from 'h3'
import { createError, getRequestURL, useSession } from 'h3'
import { $fetch, type FetchOptions } from 'ofetch'
import type {
  IdentityAuthenticationContext,
  IdentityAuthenticationExperience,
  IdentityLoginData,
  IdentityLoginInput,
  IdentityRegisterInput,
  IdentityResetPasswordInput
} from '../../shared/types/identity-auth'

type HostedConnection = {
  apiUrl: string
  project: string
  clientId: string
}

type HostedApplication = {
  key: string
  name: string
  callbackUrl: string
  applicationUrl?: string
  appearance: HostedApplicationAppearance
  primary: HostedConnection
  sandbox?: HostedConnection
}

type HostedApplicationAppearance = {
  welcomeText: string | null
  accentColor: string | null
  backgroundPreset: 'identity' | 'slate' | 'indigo' | 'emerald' | 'sunset'
  logoUrl: string | null
}

type HostedFlow = {
  application: string
  state: string
  identity: IdentityLoginData
}

type HostedAccount = {
  application: string
  connection: 'primary' | 'sandbox'
  identity: IdentityLoginData
}

type IdentityEnvelope<T> = { data: T }

const identityPrefix = '/api/v1/identity'

type HostedApplicationConfiguration = {
  key: string
  name: string
  application_url: string
  callback_url: string
  appearance?: {
    welcome_text?: string | null
    accent_color?: string | null
    background_preset?: HostedApplicationAppearance['backgroundPreset']
    logo_url?: string | null
  }
  primary: {
    project: string
    client_id: string
  }
  sandbox: { project: string, client_id: string } | null
}

function assertConnection(connection: HostedConnection | undefined): HostedConnection {
  if (!connection?.apiUrl || !connection.project || !connection.clientId) {
    throw createError({
      statusCode: 503,
      statusMessage: 'This hosted Identity application is not fully configured.'
    })
  }

  return connection
}

async function resolveHostedApplication(
  event: H3Event,
  path: string
): Promise<HostedApplication> {
  const config = useRuntimeConfig(event)
  const apiUrl = String(config.identity?.apiUrl ?? '')
  const token = String(config.identityHostedApplicationsToken ?? '')
  if (!apiUrl || !token) {
    throw createError({ statusCode: 503, statusMessage: 'Hosted Identity resolution is not configured.' })
  }

  const target = requestTarget({ apiUrl } as HostedConnection, path)
  const response = await $fetch<IdentityEnvelope<HostedApplicationConfiguration>>(target.path, {
    baseURL: target.baseURL,
    headers: { 'X-Internal-Token': token }
  })
  const application = response.data
  if (!application?.name || !application.callback_url || !application.primary) {
    throw createError({ statusCode: 502, statusMessage: 'Identity returned an invalid hosted application configuration.' })
  }

  return {
    key: application.key,
    name: application.name,
    applicationUrl: application.application_url,
    callbackUrl: application.callback_url,
    appearance: {
      welcomeText: application.appearance?.welcome_text ?? null,
      accentColor: application.appearance?.accent_color ?? null,
      backgroundPreset: application.appearance?.background_preset ?? 'identity',
      logoUrl: application.appearance?.logo_url ?? null
    },
    primary: assertConnection({
      apiUrl,
      project: application.primary.project,
      clientId: application.primary.client_id
    }),
    sandbox: application.sandbox
      ? assertConnection({ apiUrl, project: application.sandbox.project, clientId: application.sandbox.client_id })
      : undefined
  }
}

export async function identityHostedApplication(
  event: H3Event,
  application: string
): Promise<HostedApplication> {
  return await resolveHostedApplication(event, `/hosted-applications/${encodeURIComponent(application)}/configuration`)
}

export async function identityHostedApplicationByClient(
  event: H3Event,
  clientId: string
): Promise<HostedApplication> {
  return await resolveHostedApplication(event, `/hosted-clients/${encodeURIComponent(clientId)}/configuration`)
}

function requestTarget(connection: HostedConnection, path: string) {
  const baseURL = connection.apiUrl.replace(/\/+$/, '')
  const normalizedPath = path.startsWith('/') ? path : `/${path}`
  const baseIncludesPrefix = baseURL.endsWith(identityPrefix)

  if (normalizedPath.startsWith('/api/') && !normalizedPath.startsWith(identityPrefix)) {
    return {
      baseURL: baseIncludesPrefix ? baseURL.slice(0, -identityPrefix.length) : baseURL,
      path: normalizedPath
    }
  }

  if (baseIncludesPrefix && normalizedPath.startsWith(identityPrefix)) {
    return {
      baseURL,
      path: normalizedPath.slice(identityPrefix.length) || '/'
    }
  }

  return {
    baseURL,
    path: baseIncludesPrefix || normalizedPath.startsWith(identityPrefix)
      ? normalizedPath
      : `${identityPrefix}${normalizedPath}`
  }
}

async function connectionRequest<T>(
  connection: HostedConnection,
  path: string,
  options: FetchOptions<'json'> = {}
): Promise<T> {
  const target = requestTarget(connection, path)
  return await $fetch<T>(target.path, {
    baseURL: target.baseURL,
    ...options
  })
}

async function clientRequest<T>(
  connection: HostedConnection,
  path: string,
  body: Record<string, unknown>,
  accessToken?: string
): Promise<T> {
  const response = await connectionRequest<IdentityEnvelope<T>>(connection, path, {
    method: 'POST',
    body,
    headers: accessToken ? { Authorization: `Bearer ${accessToken}` } : undefined
  })

  return response.data
}

function accountSession(event: H3Event) {
  const config = useRuntimeConfig(event)
  const password = String(config.session?.password ?? '')
  if (password.length < 32) {
    throw createError({ statusCode: 503, statusMessage: 'Identity hosted sessions are not configured.' })
  }

  return useSession<HostedAccount>(event, {
    password,
    name: 'identity-hosted-account',
    maxAge: 60 * 60,
    cookie: {
      path: '/',
      httpOnly: true,
      sameSite: 'lax',
      secure: process.env.NODE_ENV === 'production'
    }
  })
}

async function hostedRequest<T>(
  event: H3Event,
  application: HostedApplication,
  path: string,
  body: Record<string, unknown> = {},
  accessToken?: string
): Promise<T> {
  const config = useRuntimeConfig(event)
  const apiUrl = String(config.identity?.apiUrl ?? '')
  const token = String(config.identityHostedApplicationsToken ?? '')
  if (!apiUrl || !token) {
    throw createError({ statusCode: 503, statusMessage: 'Hosted Identity authentication is not configured.' })
  }
  const target = requestTarget({ apiUrl, project: '', clientId: '' }, `/hosted-applications/${encodeURIComponent(application.key)}/auth${path}`)
  const response = await $fetch<IdentityEnvelope<T>>(target.path, {
    baseURL: target.baseURL,
    method: 'POST',
    body,
    headers: {
      'X-Internal-Token': token,
      ...(accessToken ? { Authorization: `Bearer ${accessToken}` } : {})
    }
  })

  return response.data
}

function assertState(state: string): string {
  if (!/^[A-Za-z0-9_-]{32,180}$/.test(state)) {
    throw createError({ statusCode: 422, statusMessage: 'The authentication state is invalid.' })
  }

  return state
}

async function handoff(
  event: H3Event,
  application: HostedApplication,
  identity: IdentityLoginData,
  state: string
): Promise<{ redirectUrl: string }> {
  const result = await hostedRequest<{ code: string, redirect_uri: string }>(
    event,
    application,
    '/handoff',
    {},
    identity.access_token
  )
  const redirect = new URL(result.redirect_uri)
  redirect.searchParams.set('code', result.code)
  redirect.searchParams.set('state', assertState(state))

  return { redirectUrl: redirect.toString() }
}

function flowSession(event: H3Event) {
  const config = useRuntimeConfig(event)
  const password = String(config.session?.password ?? '')
  if (password.length < 32) {
    throw createError({ statusCode: 503, statusMessage: 'Identity hosted sessions are not configured.' })
  }

  return useSession<HostedFlow>(event, {
    password,
    name: 'identity-hosted-flow',
    maxAge: 60 * 15,
    cookie: {
      path: '/',
      httpOnly: true,
      sameSite: 'lax',
      secure: process.env.NODE_ENV === 'production'
    }
  })
}

async function context(event: H3Event, application: HostedApplication, name: 'primary' | 'sandbox') {
  const value = await hostedRequest<Omit<IdentityAuthenticationContext, 'connection'>>(
    event, application, '/context', { connection: name }
  )

  return { ...value, connection: name }
}

export async function identityHostedExperience(
  event: H3Event,
  applicationKey: string
): Promise<IdentityAuthenticationExperience & { application: { key: string, name: string, appearance: HostedApplicationAppearance } }> {
  const application = await identityHostedApplication(event, applicationKey)
  return {
    application: { key: applicationKey, name: application.name, appearance: application.appearance },
    primary: await context(event, application, 'primary'),
    sandbox: application.sandbox ? await context(event, application, 'sandbox') : null
  }
}

export async function identityHostedAccountContext(
  event: H3Event,
  applicationKey: string
) {
  const application = await identityHostedApplication(event, applicationKey)
  const session = await accountSession(event)
  const account = session.data
  const authenticated = account.application === applicationKey && Boolean(account.identity)
  const primary = await context(event, application, 'primary')

  return {
    application: {
      key: applicationKey,
      name: application.name,
      returnUrl: application.applicationUrl ?? new URL(application.callbackUrl).origin
    },
    project: primary.project,
    authenticated,
    user: authenticated ? account.identity.identity.user : null
  }
}

export async function identityHostedAccountLogin(
  event: H3Event,
  applicationKey: string,
  input: IdentityLoginInput
) {
  const application = await identityHostedApplication(event, applicationKey)
  const identity = await hostedRequest<IdentityLoginData>(event, application, '/login', input)

  if (!identity.identity.user.email_verified) {
    throw createError({
      statusCode: 403,
      statusMessage: 'Verify your email before managing this Identity account.'
    })
  }

  const session = await accountSession(event)
  await session.update({ application: applicationKey, connection: 'primary', identity })

  return await identityHostedAccountContext(event, applicationKey)
}

export async function identityHostedAccountLogout(event: H3Event): Promise<void> {
  const session = await accountSession(event)
  const account = session.data
  try {
    if (account.application && account.identity?.access_token) {
      const application = await identityHostedApplication(event, account.application)
      const connection = account.connection === 'sandbox'
        ? assertConnection(application.sandbox)
        : application.primary
      await connectionRequest(connection, '/auth/logout', {
        method: 'POST',
        headers: { Authorization: `Bearer ${account.identity.access_token}` }
      })
    }
  } catch {
    // Clearing the encrypted browser session remains authoritative here.
  } finally {
    await session.clear()
  }
}

export async function identityHostedAccountRequest<T>(
  event: H3Event,
  applicationKey: string,
  path: string,
  options: FetchOptions<'json'> = {}
): Promise<T> {
  const application = await identityHostedApplication(event, applicationKey)
  const session = await accountSession(event)
  const account = session.data

  if (account.application !== applicationKey || !account.identity?.access_token) {
    throw createError({ statusCode: 401, statusMessage: 'Sign in to manage this Identity account.' })
  }

  const connection = account.connection === 'sandbox'
    ? assertConnection(application.sandbox)
    : application.primary
  const execute = (token: string) => connectionRequest<T>(connection, path, {
    ...options,
    headers: {
      Authorization: `Bearer ${token}`,
      ...options.headers
    }
  })
  const refresh = async () => {
    const identity = await hostedRequest<IdentityLoginData>(event, application, '/refresh', {
      refresh_token: account.identity.refresh_token
    })
    await session.update({ ...account, identity })
    return identity.access_token
  }

  try {
    const expiresAt = Date.parse(account.identity.access_token_expires_at)
    const token = Number.isFinite(expiresAt) && expiresAt <= Date.now() + 30_000
      ? await refresh()
      : account.identity.access_token
    return await execute(token)
  } catch (error) {
    const status = (error as { response?: { status?: number } }).response?.status
    if (status !== 401) throw error

    try {
      return await execute(await refresh())
    } catch (refreshError) {
      await session.clear()
      throw refreshError
    }
  }
}

export async function identityHostedLogin(
  event: H3Event,
  applicationKey: string,
  state: string,
  input: IdentityLoginInput
) {
  const application = await identityHostedApplication(event, applicationKey)
  const identity = await hostedRequest<IdentityLoginData>(event, application, '/login', input)

  if (!identity.identity.user.email_verified) {
    const session = await flowSession(event)
    await session.update({ application: applicationKey, state: assertState(state), identity })
    const verificationUrl = new URL('/auth/verify-email', getRequestURL(event).origin)
    verificationUrl.searchParams.set('application', applicationKey)
    verificationUrl.searchParams.set('state', state)

    return { redirectUrl: verificationUrl.toString() }
  }

  return await handoff(event, application, identity, state)
}

export async function identityHostedSandbox(
  event: H3Event,
  applicationKey: string,
  state: string
) {
  const application = await identityHostedApplication(event, applicationKey)
  assertConnection(application.sandbox)
  const identity = await hostedRequest<IdentityLoginData>(event, application, '/sandbox-session')

  return await handoff(event, application, identity, state)
}

export async function identityHostedRegister(
  event: H3Event,
  applicationKey: string,
  state: string,
  input: IdentityRegisterInput
) {
  const application = await identityHostedApplication(event, applicationKey)
  const identity = await hostedRequest<IdentityLoginData>(event, application, '/register', {
    username: input.username,
    email: input.email,
    password: input.password,
    password_confirmation: input.passwordConfirmation
  })

  if (identity.identity.user.email_verified) {
    return await handoff(event, application, identity, state)
  }

  const session = await flowSession(event)
  await session.update({ application: applicationKey, state: assertState(state), identity })

  return { verificationRequired: true }
}

export async function identityHostedResendVerification(event: H3Event) {
  const session = await flowSession(event)
  const flow = session.data
  if (!flow.application || !flow.identity) {
    throw createError({ statusCode: 401, statusMessage: 'The hosted verification flow has expired.' })
  }
  const application = await identityHostedApplication(event, flow.application)

  return await clientRequest<Record<string, unknown>>(
    application.primary,
    '/auth/email/verification/resend',
    {},
    flow.identity.access_token
  )
}

export async function identityHostedFlowContext(event: H3Event) {
  const session = await flowSession(event)
  const flow = session.data
  if (!flow.application || !flow.state || !flow.identity) {
    throw createError({ statusCode: 401, statusMessage: 'The hosted verification flow has expired.' })
  }

  return {
    application: flow.application,
    state: flow.state,
    email: flow.identity.identity.user.email
  }
}

export async function identityHostedVerifyEmail(event: H3Event, code: string) {
  const session = await flowSession(event)
  const flow = session.data
  if (!flow.application || !flow.state || !flow.identity) {
    throw createError({ statusCode: 401, statusMessage: 'The hosted verification flow has expired.' })
  }
  const application = await identityHostedApplication(event, flow.application)
  await clientRequest<Record<string, unknown>>(
    application.primary,
    '/auth/email/verification',
    { code },
    flow.identity.access_token
  )
  const result = await handoff(event, application, flow.identity, flow.state)
  await session.clear()

  return result
}

export async function identityHostedForgotPassword(
  event: H3Event,
  applicationKey: string,
  email: string
) {
  const application = await identityHostedApplication(event, applicationKey)
  return await hostedRequest<Record<string, unknown>>(event, application, '/password/forgot', { email })
}

export async function identityHostedResetPassword(
  event: H3Event,
  clientId: string,
  input: IdentityResetPasswordInput
) {
  const application = await identityHostedApplicationByClient(event, clientId)
  const result = await hostedRequest<Record<string, unknown>>(event, application, '/password/reset', {
    email: input.email,
    token: input.token,
    password: input.password,
    password_confirmation: input.passwordConfirmation
  })

  return {
    ...result,
    applicationUrl: application.applicationUrl ?? new URL(application.callbackUrl).origin
  }
}
