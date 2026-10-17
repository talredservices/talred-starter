import { defineEventHandler } from 'h3'
import { redirectToHostedIdentityAuth } from '#server/utils/hosted-identity-auth-redirect'

export default defineEventHandler(event => redirectToHostedIdentityAuth(event, 'starter', '/dashboard'))
