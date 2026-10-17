import {
  landingAliases,
  landingCompatibilityDate,
  landingRouteRules
} from './index'
import { fileURLToPath } from 'node:url'

const saasThemeCss = fileURLToPath(new URL('./assets/css/saas-theme.css', import.meta.url))

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  runtimeConfig: {
    talredIdentity: {
      applications: {
        starter: {
          identityApiUrl: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_IDENTITY_API_URL ?? process.env.IDENTITY_API_URL ?? 'http://localhost:8100',
          hostedAuthUrl: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_HOSTED_AUTH_URL ?? 'http://localhost:3100',
          clientId: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_CLIENT_ID ?? '',
          clientSecret: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_CLIENT_SECRET ?? '',
          hostedApplication: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_HOSTED_APPLICATION ?? 'zoltasoft-starter',
          callbackUrl: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_CALLBACK_URL ?? 'http://localhost:3300/api/identity/starter/auth/callback',
          sessionCookie: 'zolta-starter-identity-session',
          defaultRedirect: '/dashboard'
        },
        saas: {
          identityApiUrl: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_IDENTITY_API_URL ?? process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_IDENTITY_API_URL ?? process.env.IDENTITY_API_URL ?? 'http://localhost:8100',
          hostedAuthUrl: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_HOSTED_AUTH_URL ?? process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_HOSTED_AUTH_URL ?? 'http://localhost:3100',
          clientId: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_CLIENT_ID ?? process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_CLIENT_ID ?? '',
          clientSecret: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_CLIENT_SECRET ?? process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_CLIENT_SECRET ?? '',
          hostedApplication: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_HOSTED_APPLICATION ?? 'zoltasoft-saas',
          callbackUrl: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_CALLBACK_URL ?? 'http://localhost:3300/api/identity/saas/auth/callback',
          sessionCookie: 'zolta-saas-identity-session',
          defaultRedirect: '/saas/dashboard'
        }
      }
    }
  },
  modules: [],
  css: [saasThemeCss],
  alias: {
    ...landingAliases
  },
  routeRules: landingRouteRules,
  compatibilityDate: landingCompatibilityDate
})
