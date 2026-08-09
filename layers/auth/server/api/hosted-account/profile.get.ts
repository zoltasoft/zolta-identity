import { createError, defineEventHandler, getValidatedQuery } from 'h3'
import { z } from 'zod/v4'

type Profile = {
  id: string
  email: string
  username?: string
  name?: string
  avatar_url?: string | null
  email_verified?: boolean
}

type Envelope = {
  data: {
    auth_user?: Profile
    response?: { auth_user?: Profile }
  }
}

const schema = z.object({ application: z.string().trim().min(1) })

export default defineEventHandler(async (event) => {
  const { application } = await getValidatedQuery(event, schema.parse)
  const response = await identityHostedAccountRequest<Envelope>(event, application, '/api/auth/user')
  const profile = response.data.auth_user ?? response.data.response?.auth_user
  if (!profile) {
    throw createError({ statusCode: 502, statusMessage: 'Identity returned an invalid account profile.' })
  }

  return profile
})
