import { z } from 'zod/v4'

const schema = z.object({ name: z.string().min(1).max(255), slug: z.string().min(1).max(100), description: z.string().max(2000).nullable().optional(), permission_ids: z.array(z.string().uuid()) })
export default defineEventHandler(async event => identityApi(event, '/api/v1/identity/project-access-catalog/roles', { method: 'POST', body: schema.parse(await readBody(event)) }))
