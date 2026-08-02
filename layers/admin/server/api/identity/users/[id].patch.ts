import { createError, defineEventHandler, getRouterParam, readValidatedBody } from 'h3'
import { z } from 'zod/v4'

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  if (!id) throw createError({ statusCode: 400, statusMessage: 'User ID is required.' })
  const body = await readValidatedBody(event, z.object({
    is_system_admin: z.boolean(),
    locked: z.boolean()
  }).parse)
  return await identityApi(event, `/api/v1/identity/users/${id}`, { method: 'PATCH', body })
})
