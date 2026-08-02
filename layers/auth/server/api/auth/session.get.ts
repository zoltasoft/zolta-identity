import { defineEventHandler } from 'h3'

export default defineEventHandler(async (event) => {
  const { session } = await requireIdentitySession(event)
  return session.identity
})
