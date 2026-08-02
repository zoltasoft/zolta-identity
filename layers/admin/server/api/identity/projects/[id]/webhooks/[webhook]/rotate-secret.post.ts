import type { IdentityWebhook } from '#admin/types/identity-access'

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  const webhook = getRouterParam(event, 'webhook')
  const response = await identityApi<{ data: IdentityWebhook }>(event, `/api/v1/identity/projects/${id}/webhooks/${webhook}/rotate-secret`, {
    method: 'POST'
  })

  return response.data
})
