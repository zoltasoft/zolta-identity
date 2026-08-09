import { defineEventHandler, getValidatedQuery } from 'h3'
import { z } from 'zod/v4'

const schema = z.object({ application: z.string().trim().min(1) })

export default defineEventHandler(async (event) => {
  const { application } = await getValidatedQuery(event, schema.parse)
  return await identityHostedAccountContext(event, application)
})
