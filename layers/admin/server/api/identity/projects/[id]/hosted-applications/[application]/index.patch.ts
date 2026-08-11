import { createError, defineEventHandler, getRouterParam, readValidatedBody } from 'h3'
import { z } from 'zod/v4'

const appearanceSchema = z.object({
  welcome_text: z.string().trim().max(280).nullable(),
  accent_color: z.string().regex(/^#[0-9A-Fa-f]{6}$/).nullable(),
  background_preset: z.enum(['identity', 'slate', 'indigo', 'emerald', 'sunset'])
})

const schema = z.object({
  name: z.string().trim().min(2).max(200),
  primary_client_id: z.uuid(),
  sandbox_client_id: z.uuid().nullable(),
  application_url: z.url().max(2048),
  callback_url: z.url().max(2048),
  status: z.enum(['active', 'disabled']),
  appearance: appearanceSchema
})

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  const application = getRouterParam(event, 'application')
  if (!id || !application) throw createError({ statusCode: 400, statusMessage: 'Hosted application ID is required.' })
  const body = await readValidatedBody(event, schema.parse)
  return await identityApi(event, `/api/v1/identity/projects/${id}/hosted-applications/${application}`, {
    method: 'PATCH',
    body
  })
})
