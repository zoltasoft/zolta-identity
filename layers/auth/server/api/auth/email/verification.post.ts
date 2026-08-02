import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod/v4'

const schema = z.object({
  code: z.string().regex(/^\d{6}$/)
})

export default defineEventHandler(async (event) => {
  const body = await readValidatedBody(event, schema.parse)
  const response = await identityApi<{ data: Record<string, unknown> }>(
    event,
    '/auth/email/verification',
    {
      method: 'POST',
      body
    }
  )
  await markIdentityEmailVerified(event)

  return response.data
})
