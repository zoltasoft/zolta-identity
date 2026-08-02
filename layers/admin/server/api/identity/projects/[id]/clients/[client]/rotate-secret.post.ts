import { createError, defineEventHandler, getRouterParam } from 'h3'
import type { IdentityClient } from '#admin/types/identity-access'

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  const client = getRouterParam(event, 'client')
  if (!id || !client) throw createError({ statusCode: 400, statusMessage: 'Project and client IDs are required.' })
  const response = await identityApi<{ data: IdentityClient }>(event, `/api/v1/identity/projects/${id}/clients/${client}/rotate-secret`, { method: 'POST' })
  return response.data
})
