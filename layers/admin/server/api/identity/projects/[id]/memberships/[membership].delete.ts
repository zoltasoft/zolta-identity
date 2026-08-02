import { createError, defineEventHandler, getRouterParam } from 'h3'

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  const membership = getRouterParam(event, 'membership')
  if (!id || !membership) throw createError({ statusCode: 400, statusMessage: 'Project and membership IDs are required.' })
  return await identityApi(event, `/api/v1/identity/projects/${id}/memberships/${membership}`, { method: 'DELETE' })
})
