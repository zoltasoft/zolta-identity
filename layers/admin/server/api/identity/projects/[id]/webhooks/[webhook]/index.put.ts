import { z } from 'zod'

const bodySchema = z.object({
  url: z.url(),
  events: z.array(z.enum(['identity.user.expired', 'identity.user.deletion_requested'])).min(1),
  status: z.enum(['active', 'disabled'])
})

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  const webhook = getRouterParam(event, 'webhook')
  return await identityApi(event, `/api/v1/identity/projects/${id}/webhooks/${webhook}`, {
    method: 'PUT',
    body: bodySchema.parse(await readBody(event))
  })
})
