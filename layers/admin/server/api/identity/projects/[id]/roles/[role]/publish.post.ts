export default defineEventHandler(async event => identityApi(event, `/api/v1/identity/projects/${getRouterParam(event, 'id')}/roles/${getRouterParam(event, 'role')}/publish`, { method: 'POST' }))
