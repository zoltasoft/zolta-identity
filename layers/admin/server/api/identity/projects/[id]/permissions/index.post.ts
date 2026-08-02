import { createError, defineEventHandler, getRouterParam, readValidatedBody } from 'h3'
import { z } from 'zod/v4'
import type { IdentityPermission } from '#admin/types/identity-access'

const schema = z.object({
  key: z.string().min(1).max(160),
  name: z.string().max(255).nullable().optional(),
  description: z.string().max(2000).nullable().optional()
})

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  if (!id) throw createError({ statusCode: 400, statusMessage: 'Project ID is required.' })
  const body = await readValidatedBody(event, schema.parse)
  const response = await identityApi<{ data: IdentityPermission }>(event, `/api/v1/identity/projects/${id}/permissions`, { method: 'POST', body })
  return response.data
})
