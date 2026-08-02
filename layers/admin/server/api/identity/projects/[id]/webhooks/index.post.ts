import { z } from 'zod'
import type { IdentityWebhook } from '#admin/types/identity-access'

const bodySchema = z.object({
  url: z.url(),
  events: z.array(z.enum(['identity.user.expired', 'identity.user.deletion_requested'])).min(1)
})

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  const response = await identityApi<{ data: IdentityWebhook }>(event, `/api/v1/identity/projects/${id}/webhooks`, {
    method: 'POST',
    body: bodySchema.parse(await readBody(event))
  })

  return response.data
})
