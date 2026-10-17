import { defineEventHandler } from 'h3'
import { redirectToHostedIdentityAuth } from '../../../utils/hosted-identity-auth-redirect'

export default defineEventHandler(event => redirectToHostedIdentityAuth(event, 'projects', '/projects/dashboard'))
