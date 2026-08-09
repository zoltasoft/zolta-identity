import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod/v4'

const schema = z.object({
  application: z.string().trim().min(1),
  email: z.email(),
  password: z.string().min(8)
})

export default defineEventHandler(async (event) => {
  const body = await readValidatedBody(event, schema.parse)
  return await identityHostedAccountLogin(event, body.application, {
    email: body.email,
    password: body.password
  })
})
