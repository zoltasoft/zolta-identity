import { defineEventHandler } from 'h3'

export default defineEventHandler(async (event) => {
  await identityLogout(event)
  return { success: true }
})
