import { createError, defineEventHandler, getRouterParam, readFormData } from 'h3'
import type { IdentityHostedApplication } from '#admin/types/identity-access'

export default defineEventHandler(async (event) => {
  const id = getRouterParam(event, 'id')
  const application = getRouterParam(event, 'application')
  const formData = await readFormData(event)
  const logo = formData?.get('logo')
  if (!id || !application || !logo || typeof logo === 'string') {
    throw createError({ statusCode: 400, statusMessage: 'A hosted application logo file is required.' })
  }

  const response = await identityApi<{ data: IdentityHostedApplication }>(
    event,
    `/api/v1/identity/projects/${id}/hosted-applications/${application}/logo`,
    { method: 'POST', body: formData }
  )

  return response.data
})
