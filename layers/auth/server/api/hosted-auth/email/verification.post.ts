import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod/v4'
import { identityHostedVerifyEmail } from '../../../utils/identity-hosted-auth'

const schema = z.object({ code: z.string().regex(/^\d{6}$/) })

export default defineEventHandler(async (event) => {
  const body = await readValidatedBody(event, schema.parse)
  return await identityHostedVerifyEmail(event, body.code)
})
