import { defineEventHandler, getValidatedQuery } from 'h3'
import { z } from 'zod/v4'
import type { IdentityAccountSession } from '../../../../shared/types/identity-auth'

const schema = z.object({ application: z.string().trim().min(1) })

export default defineEventHandler(async (event) => {
  const { application } = await getValidatedQuery(event, schema.parse)
  const response = await identityHostedAccountRequest<{
    data: IdentityAccountSession[]
  }>(event, application, '/auth/sessions')

  return response.data
})
