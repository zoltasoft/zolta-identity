export function useAuthenticatedFetch() {
  const { isUnauthorizedError, clearSessionAndRedirect } = useUnauthorizedSessionHandler()
  const requestFetch = import.meta.server ? useRequestFetch() : $fetch

  return async function authenticatedFetch<T>(
    request: string,
    options?: Parameters<typeof $fetch>[1]
  ): Promise<T> {
    try {
      return await requestFetch<T>(request, options) as T
    } catch (error) {
      if (isUnauthorizedError(error)) {
        await clearSessionAndRedirect()
      }

      throw error
    }
  }
}
