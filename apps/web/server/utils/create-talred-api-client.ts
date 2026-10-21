import { createIdentityApiClient } from '@talred/identity-consumer-nuxt/runtime'
import type { TalredApiFetchClient } from '@talred/api-client'
import type { H3Event } from 'h3'

/** Creates an authenticated API client while keeping upstream credentials server-side. */
export function createTalredApiClient(event: H3Event, application: 'starter' | 'projects' = 'starter'): Promise<TalredApiFetchClient> {
  const baseURL = process.env.ZOLTA_STARTER_API_URL ?? process.env.ZOLTA_API_URL ?? 'http://localhost:8000'

  return createIdentityApiClient(event, application, {
    baseURL,
    internalToken: process.env.ZOLTA_INTERNAL_SERVICE_TOKEN ?? ''
  })
}
