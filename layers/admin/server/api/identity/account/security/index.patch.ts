import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod'

type LegacyEnvelope<T> = { data: T }

const schema = z.object({
  two_factor_enabled: z.boolean(),
  login_alerts_enabled: z.boolean(),
  backup_email: z.string().email().nullable()
})

export default defineEventHandler(async (event) => {
  const body = await readValidatedBody(event, schema.parse)
  const response = await identityApi<LegacyEnvelope<{ security: Record<string, unknown> }>>(
    event,
    '/api/users/profile/security',
    { method: 'PUT', body }
  )

  return response.data.security
})
