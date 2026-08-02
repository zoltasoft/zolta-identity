import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod/v4'

const schema = z.object({
  username: z.string().trim().min(2).max(100),
  email: z.email(),
  password: z.string().min(12),
  passwordConfirmation: z.string().min(12)
}).refine(
  body => body.password === body.passwordConfirmation,
  {
    message: 'The password confirmation does not match.',
    path: ['passwordConfirmation']
  }
)

export default defineEventHandler(async (event) => {
  const body = await readValidatedBody(event, schema.parse)
  return await identityRegister(event, body)
})
