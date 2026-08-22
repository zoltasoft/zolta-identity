import type { IdentityAccessCatalog } from '#admin/types/identity-access'

export default defineEventHandler(async (event) => {
  const response = await identityApi<{ data: IdentityAccessCatalog }>(event, '/api/v1/identity/project-access-catalog')
  return response.data
})
