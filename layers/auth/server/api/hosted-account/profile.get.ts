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
    user?: Profile
  }
}

const schema = z.object({ application: z.string().trim().min(1) })

export default defineEventHandler(async (event) => {
  const { application } = await getValidatedQuery(event, schema.parse)
  const response = await identityHostedAccountRequest<Envelope>(event, application, '/auth/me')
  const profile = response.data.user
  if (!profile) {
    throw createError({ statusCode: 502, statusMessage: 'Identity returned an invalid account profile.' })
  }

  return profile
})
