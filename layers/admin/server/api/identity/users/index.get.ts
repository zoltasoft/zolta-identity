import { defineEventHandler } from 'h3'
import type { IdentityInstallationUser } from '#admin/types/identity-access'

export default defineEventHandler(async (event) => {
  const response = await identityApi<{ data: IdentityInstallationUser[] }>(event, '/api/v1/identity/users')
  return response.data
})
