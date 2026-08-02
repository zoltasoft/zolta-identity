import { createError, defineEventHandler, getRouterParam, readValidatedBody } from 'h3'
import { z } from 'zod/v4'
import type { IdentityPermission } from '#admin/types/identity-access'

const schema = z.object({
  permissions: z.array(z.object({
    key: z.string().min(1).max(160),
    name: z.string().max(255).optional(),
    description: z.string().max(2000).optional()
  })).max(500)
})

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  const client = getRouterParam(event, 'client')
  if (!id || !client) throw createError({ statusCode: 400, statusMessage: 'Project and client IDs are required.' })
  const body = await readValidatedBody(event, schema.parse)
  const response = await identityApi<{ data: IdentityPermission[] }>(event, `/api/v1/identity/projects/${id}/clients/${client}/permission-manifest`, { method: 'PUT', body })
  return response.data
})
