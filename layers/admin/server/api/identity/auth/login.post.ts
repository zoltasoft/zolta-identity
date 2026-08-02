import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod/v4'

const schema = z.object({
  email: z.email(),
  password: z.string().min(8)
})

export default defineEventHandler(async (event) => {
  const body = await readValidatedBody(event, schema.parse)
  return await identityLogin(event, body)
})
