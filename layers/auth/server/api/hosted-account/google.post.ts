import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod/v4'

const schema = z.object({
  application: z.string().trim().min(1),
  tab: z.enum(['profile', 'security']).default('profile')
})

export default defineEventHandler(async (event) => {
  const body = await readValidatedBody(event, schema.parse)
  return await identityHostedAccountGoogleStart(event, body.application, body.tab)
})
