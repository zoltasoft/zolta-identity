import { defineEventHandler } from 'h3'
import type { IdentityAccountSession } from '#admin/types/identity-access'

export default defineEventHandler(async (event) => {
  const response = await identityApi<{ data: IdentityAccountSession[] }>(event, '/api/v1/identity/auth/sessions')
  return response.data
})
