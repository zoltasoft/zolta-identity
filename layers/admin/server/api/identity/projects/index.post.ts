import { defineEventHandler, readValidatedBody } from 'h3'
import { z } from 'zod/v4'
import type { IdentityProject } from '#admin/types/identity-access'

const schema = z.object({
  name: z.string().min(2).max(255),
  slug: z.string().regex(/^[a-z0-9]+(?:-[a-z0-9]+)*$/).max(100),
  description: z.string().max(2000).nullable().optional()
})

export default defineEventHandler(async (event) => {
  const body = await readValidatedBody(event, schema.parse)
  const response = await identityApi<{ data: IdentityProject }>(event, '/api/v1/identity/projects', {
    method: 'POST',
    body
  })
  return response.data
})
