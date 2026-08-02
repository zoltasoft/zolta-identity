import type { H3Event } from 'h3'
import { createError } from 'h3'
import { $fetch, type FetchOptions } from 'ofetch'
import { hash } from 'ohash'
import { toIdentityBrowserSession } from '../resources/identity-session.resource'
import type { IdentityLoginData } from '../../types/identity-access'

type IdentityEnvelope<T> = { data: T }
type IdentityRefreshRegistry = typeof globalThis & {
  __identityConsoleRefreshRequests?: Map<string, Promise<IdentityLoginData>>
}

const refreshRegistry = globalThis as IdentityRefreshRegistry
const refreshRequests = refreshRegistry.__identityConsoleRefreshRequests
  ??= new Map<string, Promise<IdentityLoginData>>()
const refreshDeduplicationWindowMs = 5_000

function identityConfiguration() {
  const clientId = process.env.IDENTITY_CLIENT_ID ?? ''
  const clientSecret = process.env.IDENTITY_CLIENT_SECRET ?? ''

  if (!clientId || !clientSecret) {
    throw createError({
      statusCode: 503,
      statusMessage: 'The identity BFF client is not configured.'
    })
  }

  return {
    baseURL: process.env.IDENTITY_API_URL ?? process.env.LARAVEL_API_URL ?? 'http://localhost:8000',
    project: process.env.IDENTITY_PROJECT ?? '',
    clientId,
    clientSecret
  }
}

export async function setIdentitySession(event: H3Event, data: IdentityLoginData) {
  await setUserSession(event, {
    user: {
      id: data.identity.user.id,
      email: data.identity.user.email,
      name: data.identity.user.username,
      username: data.identity.user.username
    },
    identity: toIdentityBrowserSession(data),
    secure: {
      identityAccessToken: data.access_token,
      identityAccessTokenExpiresAt: data.access_token_expires_at,
      identityRefreshToken: data.refresh_token,
      identityRefreshTokenExpiresAt: data.refresh_token_expires_at
    },
    lastLoggedIn: new Date()
  })
}

export async function identityLogin(event: H3Event, credentials: { email: string, password: string }) {
  const config = identityConfiguration()
  const response = await $fetch<IdentityEnvelope<IdentityLoginData>>('/api/v1/identity/auth/login', {
    baseURL: config.baseURL,
    method: 'POST',
    body: {
      project: config.project,
      client_id: config.clientId,
      client_secret: config.clientSecret,
      ...credentials
    }
  })
  await setIdentitySession(event, response.data)

  return toIdentityBrowserSession(response.data)
}

export async function requireIdentitySession(event: H3Event) {
  const session = await requireUserSession(event)
  const secure = session.secure as {
    identityAccessToken?: string | null
    identityAccessTokenExpiresAt?: string | null
    identityRefreshToken?: string | null
  }
  if (!session.identity || !secure.identityAccessToken || !secure.identityRefreshToken) {
    throw createError({ statusCode: 401, statusMessage: 'An identity session is required.' })
  }

  return { session, secure }
}

async function refreshIdentitySession(event: H3Event): Promise<string> {
  const { secure } = await requireIdentitySession(event)
  const config = identityConfiguration()
  const refreshToken = secure.identityRefreshToken as string
  const requestKey = hash(refreshToken)
  let request = refreshRequests.get(requestKey)
  if (!request) {
    const createdRequest = $fetch<IdentityEnvelope<IdentityLoginData>>('/api/v1/identity/auth/refresh', {
      baseURL: config.baseURL,
      method: 'POST',
      body: {
        client_id: config.clientId,
        client_secret: config.clientSecret,
        refresh_token: refreshToken
      }
    }).then(response => response.data)
    request = createdRequest
    refreshRequests.set(requestKey, createdRequest)
    void createdRequest.then(
      () => setTimeout(() => {
        if (refreshRequests.get(requestKey) === createdRequest) {
          refreshRequests.delete(requestKey)
        }
      }, refreshDeduplicationWindowMs),
      () => {
        if (refreshRequests.get(requestKey) === createdRequest) {
          refreshRequests.delete(requestKey)
        }
      }
    )
  }

  const data = await request
  await setIdentitySession(event, data)

  return data.access_token
}

async function freshIdentityAccessToken(event: H3Event): Promise<string> {
  const { secure } = await requireIdentitySession(event)
  const expiresAt = Date.parse(secure.identityAccessTokenExpiresAt ?? '')
  if (Number.isFinite(expiresAt) && expiresAt > Date.now() + 30_000) {
    return secure.identityAccessToken as string
  }

  return await refreshIdentitySession(event)
}

export async function identityApi<T>(event: H3Event, path: string, options: FetchOptions<'json'> = {}): Promise<T> {
  const config = identityConfiguration()
  const execute = (token: string) => $fetch<T>(path, {
    ...options,
    baseURL: config.baseURL,
    headers: {
      Accept: 'application/json',
      Authorization: `Bearer ${token}`,
      ...options.headers
    }
  })

  try {
    return await execute(await freshIdentityAccessToken(event))
  } catch (error: unknown) {
    const status = (error as { response?: { status?: number } }).response?.status
    if (status !== 401) throw error

    try {
      return await execute(await refreshIdentitySession(event))
    } catch (refreshError) {
      await clearUserSession(event)
      throw refreshError
    }
  }
}

export async function identityLogout(event: H3Event): Promise<void> {
  const { secure } = await requireIdentitySession(event)
  const config = identityConfiguration()
  try {
    await $fetch('/api/v1/identity/auth/logout', {
      baseURL: config.baseURL,
      method: 'POST',
      headers: { Authorization: `Bearer ${secure.identityAccessToken}` }
    })
  } finally {
    await clearUserSession(event)
  }
}
