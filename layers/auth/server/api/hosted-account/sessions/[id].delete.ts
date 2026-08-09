import { createError, defineEventHandler, getRouterParam, getValidatedQuery } from 'h3'
import { z } from 'zod/v4'

const schema = z.object({ application: z.string().trim().min(1) })

export default defineEventHandler(async (event) => {
  const { application } = await getValidatedQuery(event, schema.parse)
  const id = getRouterParam(event, 'id')
  if (!id) throw createError({ statusCode: 400, statusMessage: 'Session ID is required.' })

  return await identityHostedAccountRequest(event, application, `/auth/sessions/${id}`, {
    method: 'DELETE'
  })
})
