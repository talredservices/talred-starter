import { TalredApiError } from '@talred/api-client'
import { createError } from 'h3'
import { createValidationError } from './validation-error'

type HandleTalredApiErrorOptions<T extends boolean = false> = {
  notFoundAsNull?: T
}

/** Maps the Talred API client contract into stable BFF HTTP errors. */
export function handleTalredApiError<T extends boolean = false>(
  error: unknown,
  options?: HandleTalredApiErrorOptions<T>
): T extends true ? null : never {
  if (!(error instanceof TalredApiError)) throw error
  if (options?.notFoundAsNull && error.isNotFound) return null as T extends true ? null : never
  if (error.isValidation) {
    throw createValidationError({
      message: error.apiMessage || 'Validation failed',
      fieldErrors: error.fieldErrorsByField
    })
  }
  if (error.isUnauthorized) throw createError({ statusCode: 401, message: error.apiMessage })
  if (error.isForbidden) throw createError({ statusCode: 403, message: error.apiMessage })
  if (error.isNotFound) throw createError({ statusCode: 404, message: error.apiMessage })
  if (error.isServerError) throw createError({ statusCode: 502, message: 'Upstream API error' })
  throw createError({ statusCode: error.statusCode, message: error.apiMessage })
}
