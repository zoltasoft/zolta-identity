import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod/v4'

const schema = z.object({
  application: z.string().trim().min(1),
  current_password: z.string().min(8),
  password: z.string().min(8),
  password_confirmation: z.string().min(8)
}).refine(value => value.password === value.password_confirmation, {
  path: ['password_confirmation'],
  message: 'The password confirmation does not match.'
})

export default defineEventHandler(async (event) => {
  const { application, ...body } = await readValidatedBody(event, schema.parse)
  await requireIdentityHostedAccountPasswordAuthentication(event, application)
  const response = await identityHostedAccountRequest<{
    data: { message: string }
  }>(event, application, '/api/auth/password', {
    method: 'PUT',
    body
  })

  return response.data
})
