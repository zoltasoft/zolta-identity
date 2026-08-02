import { z } from 'zod'

const bodySchema = z.object({
  mode: z.enum(['live', 'sandbox']),
  sandbox_ttl_minutes: z.number().int().min(5).max(1440)
})

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  const body = bodySchema.parse(await readBody(event))

  return await identityApi(event, `/api/v1/identity/projects/${id}/environment`, {
    method: 'PATCH',
    body
  })
})
