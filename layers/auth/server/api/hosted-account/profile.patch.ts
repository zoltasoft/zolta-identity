import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod/v4'

const schema = z.object({
  application: z.string().trim().min(1),
  username: z.string().trim().min(3).max(100),
  email: z.email(),
  avatar_url: z.url().nullable()
})

export default defineEventHandler(async (event) => {
  const { application, ...body } = await readValidatedBody(event, schema.parse)
  const response = await identityHostedAccountRequest<{
    data: { profile: Record<string, unknown> }
  }>(event, application, '/api/users/profile', {
    method: 'PUT',
    body
  })

  return response.data.profile
})
