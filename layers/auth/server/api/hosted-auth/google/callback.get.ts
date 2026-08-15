import { createError, defineEventHandler, getQuery, sendRedirect } from 'h3'
import { identityHostedGoogleCallback } from '../../../utils/identity-hosted-auth'

export default defineEventHandler(async (event) => {
  const query = getQuery(event)
  if (typeof query.code !== 'string' || typeof query.state !== 'string') {
    throw createError({ statusCode: 400, statusMessage: 'Google did not complete the sign-in request.' })
  }
  const result = await identityHostedGoogleCallback(event, query.code, query.state)
  return await sendRedirect(event, result.redirectUrl, 302)
})
