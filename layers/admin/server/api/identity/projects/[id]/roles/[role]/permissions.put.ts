import { createError, defineEventHandler, getRouterParam, readValidatedBody } from 'h3'
import { z } from 'zod/v4'

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  const role = getRouterParam(event, 'role')
  if (!id || !role) throw createError({ statusCode: 400, statusMessage: 'Project and role IDs are required.' })
  const body = await readValidatedBody(event, z.object({ permission_ids: z.array(z.uuid()) }).parse)
  return await identityApi(event, `/api/v1/identity/projects/${id}/roles/${role}/permissions`, { method: 'PUT', body })
})
