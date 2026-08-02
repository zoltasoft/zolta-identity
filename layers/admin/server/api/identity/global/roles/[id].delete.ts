import { defineEventHandler, getRouterParam } from 'h3'

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  await identityApi(event, `/api/roles/${id}`, { method: 'DELETE' })

  return { success: true }
})
