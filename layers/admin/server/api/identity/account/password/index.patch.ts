import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod'

type LegacyEnvelope<T> = { data: T }

const schema = z.object({
  current_password: z.string().min(1),
  password: z.string().min(8),
  password_confirmation: z.string().min(8)
}).refine(value => value.password === value.password_confirmation, {
  path: ['password_confirmation'],
  message: 'The password confirmation does not match.'
})

export default defineEventHandler(async (event) => {
  const body = await readValidatedBody(event, schema.parse)
  const response = await identityApi<LegacyEnvelope<{ message: string }>>(
    event,
    '/api/auth/password',
    { method: 'PUT', body }
  )

  return response.data
})
