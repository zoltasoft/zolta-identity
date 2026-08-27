import { z } from 'zod/v4'

const schema = z.object({ key: z.string().min(1).max(160), name: z.string().max(255).nullable().optional(), description: z.string().max(2000).nullable().optional() })
export default defineEventHandler(async event => identityApi(event, '/api/v1/identity/project-access-catalog/permissions', { method: 'POST', body: schema.parse(await readBody(event)) }))
