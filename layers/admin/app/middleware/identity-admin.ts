export default defineNuxtRouteMiddleware(async (to) => {
  const requestFetch = useRequestFetch()
  let session: {
    isProjectAdmin: boolean
    isSystemAdmin: boolean
  }

  try {
    session = await requestFetch<{
      isProjectAdmin: boolean
      isSystemAdmin: boolean
    }>('/api/identity/auth/session')
  } catch {
    const localePath = useLocalePath()
    return navigateTo({
      path: localePath('/admin/sign-in'),
      query: { redirect: to.fullPath }
    })
  }

  if (!session.isProjectAdmin && !session.isSystemAdmin) {
    return abortNavigation(createError({
      statusCode: 403,
      statusMessage: 'Identity administrator access is required.'
    }))
  }
})
