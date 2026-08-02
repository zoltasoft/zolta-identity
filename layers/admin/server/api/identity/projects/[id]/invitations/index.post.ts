import { createError, defineEventHandler, getRouterParam, readValidatedBody } from 'h3'
import { z } from 'zod/v4'

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  if (!id) throw createError({ statusCode: 400, statusMessage: 'Project ID is required.' })
  const body = await readValidatedBody(event, z.object({ email: z.email(), is_admin: z.boolean().default(false) }).parse)
  const response = await identityApi<{ data: Record<string, unknown> }>(event, `/api/v1/identity/projects/${id}/invitations`, { method: 'POST', body })
  return response.data
})
