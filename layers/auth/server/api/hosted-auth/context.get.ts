import { defineEventHandler, getValidatedQuery } from 'h3'
import { z } from 'zod/v4'
import { identityHostedExperience } from '../../utils/identity-hosted-auth'

const schema = z.object({ application: z.string().trim().min(1).max(100) })

export default defineEventHandler(async (event) => {
  const query = await getValidatedQuery(event, schema.parse)
  return await identityHostedExperience(event, query.application)
})
