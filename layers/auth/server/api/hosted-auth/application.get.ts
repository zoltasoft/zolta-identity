import { defineEventHandler, getValidatedQuery, setResponseHeader } from 'h3'
import { z } from 'zod/v4'
import { identityHostedApplicationMetadata } from '../../utils/identity-hosted-auth'

const schema = z.object({
  application: z.string().trim().min(1).max(100).optional(),
  clientId: z.uuid().optional()
}).refine(query => Boolean(query.application || query.clientId), {
  message: 'An application or client ID is required.'
})

export default defineEventHandler(async (event) => {
  setResponseHeader(event, 'cache-control', 'private, max-age=60')
  const query = await getValidatedQuery(event, schema.parse)
  return {
    application: await identityHostedApplicationMetadata(event, query.application, query.clientId)
  }
})
