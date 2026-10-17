import { z } from 'zod/v4'
import { createValidationError } from '#server/http'

export const projectIdSchema = z.string().trim().uuid()

export const taskFiltersSchema = z.object({
  status: z.enum(['todo', 'in_progress', 'done']).optional(),
  priority: z.enum(['low', 'medium', 'high']).optional(),
  search: z.string().trim().max(120).optional()
}).strict()

export const createProjectSchema = z.object({
  name: z.string().trim().min(1).max(120),
  key: z.string().trim().min(1).max(32).regex(/^[A-Za-z0-9_-]+$/),
  description: z.string().trim().max(1000).optional()
}).strict()

export const createTaskSchema = z.object({
  title: z.string().trim().min(1).max(160),
  description: z.string().trim().max(2000).optional(),
  priority: z.enum(['low', 'medium', 'high']).default('medium')
}).strict()

export const updateTaskSchema = z.object({
  status: z.enum(['todo', 'in_progress', 'done']).optional(),
  priority: z.enum(['low', 'medium', 'high']).optional(),
  title: z.string().trim().min(1).max(160).optional(),
  description: z.string().trim().max(2000).optional()
}).strict().refine(value => Object.keys(value).length > 0, 'At least one task field is required.')

export function parseProjectId(value: string | undefined, field: string) {
  const result = projectIdSchema.safeParse(value)

  if (!result.success) {
    throw createValidationError({
      statusCode: 400,
      message: 'A valid identifier is required.',
      fieldErrors: { [field]: ['Enter a valid identifier.'] }
    })
  }

  return result.data
}
