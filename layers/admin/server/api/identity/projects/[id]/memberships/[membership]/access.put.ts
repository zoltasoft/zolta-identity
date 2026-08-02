import { createError, defineEventHandler, getRouterParam, readValidatedBody } from 'h3'
import { z } from 'zod/v4'

const schema = z.object({
  role_ids: z.array(z.uuid()),
  permission_ids: z.array(z.uuid()),
  is_admin: z.boolean(),
  status: z.enum(['active', 'suspended'])
})

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  const membership = getRouterParam(event, 'membership')
  if (!id || !membership) throw createError({ statusCode: 400, statusMessage: 'Project and membership IDs are required.' })
  const body = await readValidatedBody(event, schema.parse)
  return await identityApi(event, `/api/v1/identity/projects/${id}/memberships/${membership}/access`, { method: 'PUT', body })
})
