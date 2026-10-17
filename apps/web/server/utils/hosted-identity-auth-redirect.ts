import { createError, getQuery, getRouterParam, sendRedirect, type H3Event } from 'h3'
import { z } from 'zod/v4'

const actionIntents = {
  'login': 'login',
  'signup': 'register',
  'register': 'register',
  'forgot-password': 'forgot-password',
  'reset-password': 'reset-password'
} as const

const authPrefillSchema = z.object({
  email: z.string().trim().email().max(254).optional(),
  token: z.string().min(32).max(128).optional()
}).strict()

function isSafeLocalReturnTo(value: string): boolean {
  if (
    value.length > 2048
    || !value.startsWith('/')
    || value.startsWith('//')
    || value.includes('\\')
    || [...value].some((character) => {
      const codePoint = character.codePointAt(0)
      return codePoint !== undefined && (codePoint < 0x20 || codePoint === 0x7f)
    })
  ) {
    return false
  }

  try {
    return new URL(value, 'https://starter.invalid').origin === 'https://starter.invalid'
  } catch {
    return false
  }
}

export function redirectToHostedIdentityAuth(event: H3Event, application: string, fallback: string) {
  const action = getRouterParam(event, 'action') ?? ''
  const intent = actionIntents[action as keyof typeof actionIntents]
  if (!intent) {
    throw createError({ statusCode: 404, statusMessage: 'Page not found.' })
  }

  const query = getQuery(event)
  const prefill = authPrefillSchema.safeParse({
    ...(typeof query.email === 'string' && query.email ? { email: query.email } : {}),
    ...(typeof query.token === 'string' && query.token ? { token: query.token } : {})
  })

  if (!prefill.success) {
    throw createError({ statusCode: 400, statusMessage: 'Invalid authentication request.' })
  }

  const returnTo = typeof query.redirect === 'string' && isSafeLocalReturnTo(query.redirect)
    ? query.redirect
    : fallback
  const params = new URLSearchParams({ intent, returnTo })
  if (prefill.data.email) params.set('email', prefill.data.email)
  if (prefill.data.token) params.set('token', prefill.data.token)

  return sendRedirect(event, `/api/identity/${encodeURIComponent(application)}/auth/authorize?${params}`, 302)
}
