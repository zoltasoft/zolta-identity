import { createError, defineEventHandler, getRouterParam } from 'h3'
import type { IdentityProject } from '#admin/types/identity-access'

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  if (!id) throw createError({ statusCode: 400, statusMessage: 'Project ID is required.' })
  const response = await identityApi<{ data: IdentityProject }>(event, `/api/v1/identity/projects/${id}/deletion/cancel`, { method: 'POST' })
  return response.data
})
