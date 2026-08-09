import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod/v4'
import { identityHostedResetPassword } from '../../../utils/identity-hosted-auth'

const schema = z.object({
  clientId: z.uuid(),
  email: z.email(),
  token: z.string().min(64),
  password: z.string().min(12),
  passwordConfirmation: z.string().min(12)
}).refine(body => body.password === body.passwordConfirmation, {
  message: 'The password confirmation does not match.',
  path: ['passwordConfirmation']
})

export default defineEventHandler(async (event) => {
  const body = await readValidatedBody(event, schema.parse)
  return await identityHostedResetPassword(event, body.clientId, body)
})
