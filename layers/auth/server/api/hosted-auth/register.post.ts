import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod/v4'
import { identityHostedRegister } from '../../utils/identity-hosted-auth'

const schema = z.object({
  application: z.string().trim().min(1).max(100),
  state: z.string().min(32).max(180),
  username: z.string().trim().min(2).max(100),
  email: z.email(),
  password: z.string().min(12),
  passwordConfirmation: z.string().min(12),
  termsAccepted: z.boolean().optional()
}).refine(body => body.password === body.passwordConfirmation, {
  message: 'The password confirmation does not match.',
  path: ['passwordConfirmation']
})

export default defineEventHandler(async (event) => {
  const body = await readValidatedBody(event, schema.parse)
  return await identityHostedRegister(event, body.application, body.state, body)
})
