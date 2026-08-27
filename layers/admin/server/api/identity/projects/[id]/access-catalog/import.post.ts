import { z } from 'zod/v4'

const schema = z.object({ permission_ids: z.array(z.string().uuid()), role_ids: z.array(z.string().uuid()) })
export default defineEventHandler(async event => identityApi(event, `/api/v1/identity/projects/${getRouterParam(event, 'id')}/access-catalog/import`, { method: 'POST', body: schema.parse(await readBody(event)) }))
