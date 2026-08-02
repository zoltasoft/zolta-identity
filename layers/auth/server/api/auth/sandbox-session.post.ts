import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod/v4'

const schema = z.object({
  connection: z.enum(['primary', 'sandbox']).default('sandbox')
})

export default defineEventHandler(async (event) => {
  const body = await readValidatedBody(event, value => schema.parse(value ?? {}))
  return await identitySandboxSession(event, body.connection)
})
