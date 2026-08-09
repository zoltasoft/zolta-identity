type IdentityMutationOptions = {
  method: 'POST' | 'PUT' | 'PATCH' | 'DELETE'
  body?: Record<string, unknown>
}

/** Sends an Identity BFF mutation with the CSRF header expected by nuxt-csurf. */
export function useIdentityMutation() {
  const { csrf, headerName } = useCsrf()

  return async <T>(path: string, options: IdentityMutationOptions): Promise<T> =>
    await $fetch<T>(path, {
      ...options,
      headers: { [headerName]: csrf }
    }) as T
}
