import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod/v4'
import { identityHostedLogin } from '../../utils/identity-hosted-auth'

const schema = z.object({
  application: z.string().trim().min(1).max(100),
  state: z.string().min(32).max(180),
  email: z.email(),
  password: z.string().min(8)
})

export default defineEventHandler(async (event) => {
  const body = await readValidatedBody(event, schema.parse)
  return await identityHostedLogin(event, body.application, body.state, {
    email: body.email,
    password: body.password
  })
})
