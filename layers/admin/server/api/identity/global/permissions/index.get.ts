import { defineEventHandler } from 'h3'
import type { IdentityGlobalPermission } from '../../../../../types/identity-access'

type LegacyEnvelope<T> = { data: T }

export default defineEventHandler(async (event) => {
  const response = await identityApi<LegacyEnvelope<{ permissions: IdentityGlobalPermission[] }>>(
    event,
    '/api/permissions'
  )

  return response.data.permissions
})
