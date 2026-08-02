import { defineEventHandler } from 'h3'
import type { IdentityProject } from '#admin/types/identity-access'

export default defineEventHandler(async (event) => {
  const response = await identityApi<{ data: IdentityProject[] }>(event, '/api/v1/identity/projects')
  return response.data
})
