import { z } from 'zod'

const bodySchema = z.object({
  registration_mode: z.enum(['invite_only', 'public']),
  registration_role_id: z.string().uuid().nullable(),
  email_verification_required: z.boolean()
})

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  const body = bodySchema.parse(await readBody(event))

  return await identityApi(event, `/api/v1/identity/projects/${id}/registration`, {
    method: 'PATCH',
    body
  })
})
