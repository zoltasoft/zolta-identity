import type { IdentityAuthenticationExperience } from '../../../shared/types/identity-auth'

export default defineNuxtRouteMiddleware(async (to) => {
  const application = typeof to.query.application === 'string' ? to.query.application : ''
  const experience = await $fetch<IdentityAuthenticationExperience>(
    application ? '/api/hosted-auth/context' : '/api/auth/context',
    {
      query: application
        ? {
            application,
            intent: typeof to.query.intent === 'string' ? to.query.intent : undefined,
            state: typeof to.query.state === 'string' ? to.query.state : undefined
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
