import { defineEventHandler, getValidatedQuery } from 'h3'
import { z } from 'zod/v4'
import { identityHostedExperience, identityHostedExperienceByClient } from '../../utils/identity-hosted-auth'

const schema = z.object({
  application: z.string().trim().min(1).max(100).optional(),
  clientId: z.uuid().optional(),
  intent: z.string().trim().min(64).max(180).optional(),
  state: z.string().trim().min(32).max(180).optional()
}).refine(query => Boolean(query.application || query.clientId), {
  message: 'An application or client ID is required.'
})

export default defineEventHandler(async (event) => {
  const query = await getValidatedQuery(event, schema.parse)
  return query.application
    ? await identityHostedExperience(event, query.application, query.intent, query.state)
    : await identityHostedExperienceByClient(event, query.clientId!)
})
