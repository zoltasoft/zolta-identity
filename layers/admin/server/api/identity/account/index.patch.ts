import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod'

type LegacyEnvelope<T> = { data: T }

const schema = z.object({
  username: z.string().trim().min(3).max(100),
  email: z.string().email(),
  avatar_url: z.string().url().nullable()
})

export default defineEventHandler(async (event) => {
  const body = await readValidatedBody(event, schema.parse)
  const response = await identityApi<LegacyEnvelope<{ profile: Record<string, unknown> }>>(
    event,
    '/api/users/profile',
    { method: 'PUT', body }
  )

  return response.data.profile
})
