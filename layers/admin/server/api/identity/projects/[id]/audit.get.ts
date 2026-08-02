import { createError, defineEventHandler, getQuery, getRouterParam } from 'h3'
import type { IdentityAuditEvent } from '#admin/types/identity-access'

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  if (!id) throw createError({ statusCode: 400, statusMessage: 'Project ID is required.' })
  const query = getQuery(event)
  const response = await identityApi<{ data: IdentityAuditEvent[] }>(event, `/api/v1/identity/projects/${id}/audit`, { query })
  return response.data
})
