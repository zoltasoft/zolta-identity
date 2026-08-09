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
  clientSecret: string
}

type HostedApplication = {
  name: string
  callbackUrl: string
  applicationUrl?: string
  primary: HostedConnection
  sandbox?: HostedConnection
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

function hostedApplications(event: H3Event): Record<string, HostedApplication> {
  return (useRuntimeConfig(event).identityHostedApplications ?? {}) as Record<string, HostedApplication>
}

function assertConnection(connection: HostedConnection | undefined): HostedConnection {
  if (!connection?.apiUrl || !connection.project || !connection.clientId || !connection.clientSecret) {
    throw createError({
      statusCode: 503,
      statusMessage: 'This hosted Identity application is not fully configured.'
    })
  }

  return connection
}

export function identityHostedApplication(
  event: H3Event,
  application: string
): HostedApplication {
  const configured = hostedApplications(event)[application]
  if (!configured?.name || !configured.callbackUrl) {
    throw createError({ statusCode: 404, statusMessage: 'Hosted Identity application not found.' })
  }

  return {
    ...configured,
    primary: assertConnection(configured.primary),
    ...(configured.sandbox ? { sandbox: assertConnection(configured.sandbox) } : {})
  }
}

export function identityHostedApplicationByClient(
  event: H3Event,
  clientId: string
): { key: string, application: HostedApplication } {
  const match = Object.entries(hostedApplications(event)).find(([, application]) => (
    application.primary?.clientId === clientId || application.sandbox?.clientId === clientId
  ))
  if (!match) {
    throw createError({ statusCode: 404, statusMessage: 'Hosted Identity application not found.' })
  }

  return { key: match[0], application: identityHostedApplication(event, match[0]) }
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

function credentials(connection: HostedConnection) {
  return {
    project: connection.project,
    client_id: connection.clientId,
    client_secret: connection.clientSecret
  }
}

function assertState(state: string): string {
  if (!/^[A-Za-z0-9_-]{32,180}$/.test(state)) {
    throw createError({ statusCode: 422, statusMessage: 'The authentication state is invalid.' })
  }

  return state
}

async function handoff(
  connection: HostedConnection,
  application: HostedApplication,
  identity: IdentityLoginData,
  state: string
): Promise<{ redirectUrl: string }> {
  const result = await clientRequest<{ code: string, redirect_uri: string }>(
    connection,
    '/auth/handoff',
    {
      client_id: connection.clientId,
      client_secret: connection.clientSecret,
      redirect_uri: application.callbackUrl
    },
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

async function context(connection: HostedConnection, name: 'primary' | 'sandbox') {
  const value = await clientRequest<Omit<IdentityAuthenticationContext, 'connection'>>(
    connection,
    '/auth/context',
    credentials(connection)
  )

  return { ...value, connection: name }
}

export async function identityHostedExperience(
  event: H3Event,
  applicationKey: string
): Promise<IdentityAuthenticationExperience & { application: { key: string, name: string } }> {
  const application = identityHostedApplication(event, applicationKey)
  return {
    application: { key: applicationKey, name: application.name },
    primary: await context(application.primary, 'primary'),
    sandbox: application.sandbox ? await context(application.sandbox, 'sandbox') : null
  }
}

export async function identityHostedAccountContext(
  event: H3Event,
  applicationKey: string
) {
  const application = identityHostedApplication(event, applicationKey)
  const session = await accountSession(event)
  const account = session.data
  const authenticated = account.application === applicationKey && Boolean(account.identity)
  const primary = await context(application.primary, 'primary')

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
  const application = identityHostedApplication(event, applicationKey)
  const identity = await clientRequest<IdentityLoginData>(application.primary, '/auth/login', {
    ...credentials(application.primary),
    ...input
  })

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
      const application = identityHostedApplication(event, account.application)
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
  const application = identityHostedApplication(event, applicationKey)
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
    const identity = await clientRequest<IdentityLoginData>(connection, '/auth/refresh', {
      client_id: connection.clientId,
      client_secret: connection.clientSecret,
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
  const application = identityHostedApplication(event, applicationKey)
  const identity = await clientRequest<IdentityLoginData>(application.primary, '/auth/login', {
    ...credentials(application.primary),
    ...input
  })

  if (!identity.identity.user.email_verified) {
    const session = await flowSession(event)
    await session.update({ application: applicationKey, state: assertState(state), identity })
    const verificationUrl = new URL('/auth/verify-email', getRequestURL(event).origin)
    verificationUrl.searchParams.set('application', applicationKey)
    verificationUrl.searchParams.set('state', state)

    return { redirectUrl: verificationUrl.toString() }
  }

  return await handoff(application.primary, application, identity, state)
}

export async function identityHostedSandbox(
  event: H3Event,
  applicationKey: string,
  state: string
) {
  const application = identityHostedApplication(event, applicationKey)
  const connection = assertConnection(application.sandbox)
  const identity = await clientRequest<IdentityLoginData>(connection, '/auth/sandbox-session', {
    client_id: connection.clientId,
    client_secret: connection.clientSecret
  })

  return await handoff(connection, application, identity, state)
}

export async function identityHostedRegister(
  event: H3Event,
  applicationKey: string,
  state: string,
  input: IdentityRegisterInput
) {
  const application = identityHostedApplication(event, applicationKey)
  const identity = await clientRequest<IdentityLoginData>(application.primary, '/auth/register', {
    ...credentials(application.primary),
    username: input.username,
    email: input.email,
    password: input.password,
    password_confirmation: input.passwordConfirmation
  })
  const session = await flowSession(event)
  await session.update({ application: applicationKey, state: assertState(state), identity })

  return { verificationRequired: !identity.identity.user.email_verified }
}

export async function identityHostedResendVerification(event: H3Event) {
  const session = await flowSession(event)
  const flow = session.data
  if (!flow.application || !flow.identity) {
    throw createError({ statusCode: 401, statusMessage: 'The hosted verification flow has expired.' })
  }
  const application = identityHostedApplication(event, flow.application)

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
  const application = identityHostedApplication(event, flow.application)
  await clientRequest<Record<string, unknown>>(
    application.primary,
    '/auth/email/verification',
    { code },
    flow.identity.access_token
  )
  const result = await handoff(application.primary, application, flow.identity, flow.state)
  await session.clear()

  return result
}

export async function identityHostedForgotPassword(
  event: H3Event,
  applicationKey: string,
  email: string
) {
  const application = identityHostedApplication(event, applicationKey)
  return await clientRequest<Record<string, unknown>>(application.primary, '/auth/password/forgot', {
    ...credentials(application.primary),
    email
  })
}

export async function identityHostedResetPassword(
  event: H3Event,
  clientId: string,
  input: IdentityResetPasswordInput
) {
  const { application } = identityHostedApplicationByClient(event, clientId)
  const result = await clientRequest<Record<string, unknown>>(application.primary, '/auth/password/reset', {
    ...credentials(application.primary),
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
