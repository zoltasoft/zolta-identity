import { FetchError } from 'ofetch'

export function identitySafeRedirect(
  value: unknown,
  fallback: string
): string {
  if (
    typeof value === 'string'
    && value.startsWith('/')
    && !value.startsWith('//')
  ) {
    return value
  }

  return fallback
}

export function identityAuthErrorMessage(
  error: unknown,
  fallback: string
): string {
  const candidate = error as {
    data?: {
      message?: string
      statusMessage?: string
    }
    message?: string
  }

  return candidate.data?.message
    ?? candidate.data?.statusMessage
    ?? candidate.message
    ?? fallback
}

/** Prevent credential failures from exposing transport jargon or account state. */
export function identityLoginErrorMessage(error: unknown): string {
  const fallback = 'We could not sign you in with those credentials.'

  return error instanceof FetchError
    && (error.status === 401 || error.response?.status === 401)
    ? fallback
    : identityAuthErrorMessage(error, fallback)
}
