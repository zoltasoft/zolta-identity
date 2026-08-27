import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod/v4'
import { identityHostedLogoutHandoff } from '../../utils/identity-hosted-auth'

const schema = z.object({
  application: z.string().trim().min(1).max(100),
  intent: z.string().trim().min(64).max(180)
})

export default defineEventHandler(async (event) => {
  const input = await readValidatedBody(event, schema.parse)
  return { redirectUrl: await identityHostedLogoutHandoff(event, input.application, input.intent) }
})
