import { createError, defineEventHandler, getRouterParam, readValidatedBody } from 'h3'
import { z } from 'zod/v4'
import type { IdentityProject } from '#admin/types/identity-access'

const schema = z.object({ confirmation: z.string().min(1).max(100) })

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  if (!id) throw createError({ statusCode: 400, statusMessage: 'Project ID is required.' })
  const body = await readValidatedBody(event, schema.parse)
  const response = await identityApi<{ data: IdentityProject }>(event, `/api/v1/identity/projects/${id}/suspension`, { method: 'POST', body })
  return response.data
})
