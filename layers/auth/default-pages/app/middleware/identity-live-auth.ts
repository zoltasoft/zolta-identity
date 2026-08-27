import type { IdentityAuthenticationExperience } from '../../../shared/types/identity-auth'

export default defineNuxtRouteMiddleware(async (to) => {
  const requestFetch = useRequestFetch()
  const application = typeof to.query.application === 'string' ? to.query.application : ''
  const state = typeof to.query.state === 'string' ? to.query.state : ''
  const intent = typeof to.query.intent === 'string' ? to.query.intent : ''

  if (application && state && intent) {
    const screen = to.path.split('/').filter(Boolean).at(-1)
    if (
      screen === 'login'
      || screen === 'register'
      || screen === 'forgot-password'
      || screen === 'reset-password'
    ) {
      return navigateTo({
        path: '/api/hosted-auth/entry',
        query: {
          application,
          state,
          intent,
          screen,
          email: typeof to.query.email === 'string' ? to.query.email : undefined,
          token: typeof to.query.token === 'string' ? to.query.token : undefined
        }
      }, { external: true })
    }
  }

  const experience = await requestFetch<IdentityAuthenticationExperience>(
    application ? '/api/hosted-auth/context' : '/api/auth/context',
    {
      query: application
        ? {
            application
          }
        : undefined
    }
  )

  if (experience.primary.project.mode !== 'live') {
    return navigateTo({ path: '/auth/login', query: application ? to.query : {} })
  }

  if (
    to.path === '/auth/register'
    && experience.primary.project.registration_mode !== 'public'
  ) {
    return navigateTo({ path: '/auth/login', query: application ? to.query : {} })
  }
})
