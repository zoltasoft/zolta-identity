import type { IdentityAuthenticationExperience } from '../../../shared/types/identity-auth'

export default defineNuxtRouteMiddleware(async (to) => {
  const experience = await $fetch<IdentityAuthenticationExperience>('/api/auth/context')

  if (experience.primary.project.mode !== 'live') {
    return navigateTo('/auth/login')
  }

  if (
    to.path === '/auth/register'
    && experience.primary.project.registration_mode !== 'public'
  ) {
    return navigateTo('/auth/login')
  }
})
