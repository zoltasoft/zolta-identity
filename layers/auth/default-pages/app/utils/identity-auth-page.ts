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
