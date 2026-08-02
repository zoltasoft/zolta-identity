import { createError, defineEventHandler, getRouterParam } from 'h3'
import type { IdentityProjectDetails } from '#admin/types/identity-access'

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  if (!id) throw createError({ statusCode: 400, statusMessage: 'Project ID is required.' })
  const response = await identityApi<{ data: IdentityProjectDetails }>(event, `/api/v1/identity/projects/${id}`)
  return response.data
})
