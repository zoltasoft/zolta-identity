import { createError, defineEventHandler, getRouterParam, readValidatedBody } from 'h3'
import { z } from 'zod/v4'

const schema = z.object({ confirmation: z.string().min(1).max(160) })

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  const permission = getRouterParam(event, 'permission')
  if (!id || !permission) throw createError({ statusCode: 400, statusMessage: 'Project and permission IDs are required.' })
  const body = await readValidatedBody(event, schema.parse)
  return await identityApi(event, `/api/v1/identity/projects/${id}/permissions/${permission}`, { method: 'DELETE', body })
})
