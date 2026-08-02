import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod'
import type { IdentityGlobalPermission } from '../../../../../types/identity-access'

type LegacyEnvelope<T> = { data: T }

const schema = z.object({
  name: z.string().trim().min(2).max(255),
  description: z.string().trim().max(255).nullable().optional()
})

export default defineEventHandler(async (event) => {
  const body = await readValidatedBody(event, schema.parse)
  const response = await identityApi<LegacyEnvelope<{ permission: IdentityGlobalPermission }>>(
    event,
    '/api/permissions',
    { method: 'POST', body }
  )

  return response.data.permission
})
