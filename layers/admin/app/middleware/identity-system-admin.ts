export default defineNuxtRouteMiddleware(async (to) => {
  const requestFetch = useRequestFetch()
  let session: { isSystemAdmin: boolean }

  try {
    session = await requestFetch<{ isSystemAdmin: boolean }>('/api/auth/session')
  } catch {
    const localePath = useLocalePath()
    return navigateTo({
      path: localePath('/admin/sign-in'),
      query: { redirect: to.fullPath }
    })
  }

  if (!session.isSystemAdmin) {
    return abortNavigation(createError({
      statusCode: 403,
      statusMessage: 'Installation administrator access is required.'
    }))
  }
})
