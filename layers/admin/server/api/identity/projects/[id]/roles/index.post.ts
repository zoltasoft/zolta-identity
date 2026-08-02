import { createError, defineEventHandler, getRouterParam, readValidatedBody } from 'h3'
import { z } from 'zod/v4'
import type { IdentityRole } from '#admin/types/identity-access'

const schema = z.object({
  name: z.string().min(2).max(255),
  slug: z.string().regex(/^[a-z0-9]+(?:-[a-z0-9]+)*$/).max(100),
  description: z.string().max(2000).nullable().optional()
})

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  if (!id) throw createError({ statusCode: 400, statusMessage: 'Project ID is required.' })
  const body = await readValidatedBody(event, schema.parse)
  const response = await identityApi<{ data: IdentityRole }>(event, `/api/v1/identity/projects/${id}/roles`, { method: 'POST', body })
  return response.data
})
