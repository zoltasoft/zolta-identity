import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod'
import type { IdentityGlobalRole } from '../../../../../types/identity-access'

type LegacyEnvelope<T> = { data: T }

const schema = z.object({
  name: z.string().trim().min(2).max(255),
  description: z.string().trim().max(255).nullable().optional(),
  permission_ids: z.array(z.string().uuid()).default([])
})

export default defineEventHandler(async (event) => {
  const body = await readValidatedBody(event, schema.parse)
  const response = await identityApi<LegacyEnvelope<{ role: IdentityGlobalRole }>>(
    event,
    '/api/roles',
    { method: 'POST', body }
  )

  return response.data.role
})
