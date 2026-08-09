import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod/v4'

const schema = z.object({
  application: z.string().trim().min(1),
  confirmation: z.literal('DELETE')
})

export default defineEventHandler(async (event) => {
  const { application } = await readValidatedBody(event, schema.parse)
  const result = await identityHostedAccountRequest(event, application, '/api/auth/account', {
    method: 'DELETE'
  })
  await identityHostedAccountLogout(event)

  return result
})
