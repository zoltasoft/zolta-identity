import { createError, defineEventHandler, getRouterParam, readValidatedBody } from 'h3'
import { z } from 'zod/v4'

const schema = z.object({ confirmation: z.string().min(1).max(255) })

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  const client = getRouterParam(event, 'client')
  if (!id || !client) throw createError({ statusCode: 400, statusMessage: 'Project and client IDs are required.' })
  const body = await readValidatedBody(event, schema.parse)
  return await identityApi(event, `/api/v1/identity/projects/${id}/clients/${client}`, { method: 'DELETE', body })
})
