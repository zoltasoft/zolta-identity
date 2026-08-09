import { defineEventHandler, getValidatedQuery } from 'h3'
import { z } from 'zod/v4'

const schema = z.object({ application: z.string().trim().min(1) })

export default defineEventHandler(async (event) => {
  const { application } = await getValidatedQuery(event, schema.parse)
  const response = await identityHostedAccountRequest<{
    data: { account_export: Record<string, unknown> }
  }>(event, application, '/api/auth/account/export')

  return response.data.account_export
})
