import { validateRequestData } from '#server/validation'
import { createError } from 'h3'
import { z } from 'zod/v4'

const workspaceId = '[0-9a-fA-F]{8}-(?:[0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}'
const workspaceNameSchema = z.string().trim().min(1).max(120)
const roleSchema = z.enum(['admin', 'member'])

const requestSchemas = [
  {
    path: new RegExp(`^workspaces/${workspaceId}$`),
    method: 'PATCH',
    schema: z.object({ name: workspaceNameSchema })
  },
  {
    path: new RegExp(`^workspaces/${workspaceId}/members/${workspaceId}$`),
    method: 'PATCH',
    schema: z.object({ role: roleSchema })
  },
  {
    path: new RegExp(`^workspaces/${workspaceId}/ownership$`),
    method: 'POST',
    schema: z.object({ account_id: z.string().uuid() })
  },
  {
    path: new RegExp(`^workspaces/${workspaceId}/invitations$`),
    method: 'POST',
    schema: z.object({
      email: z.string().trim().email().max(254),
      role: roleSchema
    })
  },
  {
    path: /^invitations\/accept$/,
    method: 'POST',
    schema: z.object({ token: z.string().min(32).max(128) })
  },
  {
    path: new RegExp(`^workspaces/${workspaceId}/resources/identity-projects$`),
    method: 'POST',
    schema: z.object({ identity_project_id: z.string().uuid() })
  }
] as const

export function validatePlatformOrganizationBody(path: string, method: string, data: unknown): Record<string, unknown> | undefined {
  const normalizedMethod = method.toUpperCase()
  if (['GET', 'HEAD', 'DELETE'].includes(normalizedMethod)) return undefined

  const request = requestSchemas.find(entry => entry.method === normalizedMethod && entry.path.test(path))
  if (!request) {
    throw createError({ statusCode: 400, statusMessage: 'Invalid organization request.' })
  }

  return validateRequestData({
    schema: request.schema,
    data,
    statusCode: 400,
    statusMessage: 'Invalid organization request.',
    strict: true
  })
}
