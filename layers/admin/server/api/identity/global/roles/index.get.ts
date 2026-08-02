import { defineEventHandler } from 'h3'
import type { IdentityGlobalRole } from '../../../../../types/identity-access'

type LegacyEnvelope<T> = { data: T }

export default defineEventHandler(async (event) => {
  const response = await identityApi<LegacyEnvelope<{ roles: IdentityGlobalRole[] }>>(
    event,
    '/api/roles'
  )

  return response.data.roles
})
