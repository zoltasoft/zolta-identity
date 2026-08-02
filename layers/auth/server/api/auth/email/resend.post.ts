import { defineEventHandler } from 'h3'

export default defineEventHandler(async (event) => {
  const response = await identityApi<{ data: Record<string, unknown> }>(
    event,
    '/auth/email/verification/resend',
    { method: 'POST' }
  )

  return response.data
})
