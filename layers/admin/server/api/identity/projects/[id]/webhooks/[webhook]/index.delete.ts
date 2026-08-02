export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  const webhook = getRouterParam(event, 'webhook')
  return await identityApi(event, `/api/v1/identity/projects/${id}/webhooks/${webhook}`, {
    method: 'DELETE'
  })
})
