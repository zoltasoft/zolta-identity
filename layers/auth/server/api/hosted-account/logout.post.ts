import { defineEventHandler } from 'h3'

export default defineEventHandler(async (event) => {
  await identityHostedAccountLogout(event)
  return { success: true }
})
