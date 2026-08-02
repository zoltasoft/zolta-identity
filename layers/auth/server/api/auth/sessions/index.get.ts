import { defineEventHandler } from 'h3'
import type { IdentityAccountSession } from '../../../../shared/types/identity-auth'

export default defineEventHandler(async (event) => {
  const response = await identityApi<{ data: IdentityAccountSession[] }>(
    event,
    '/auth/sessions'
  )

  return response.data
})
