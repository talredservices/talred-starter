const starterIdentityApplication = {
  mode: 'hosted',
  identityApiUrl: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_IDENTITY_API_URL ?? 'http://localhost:8100',
  hostedAuthUrl: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_HOSTED_AUTH_URL ?? 'http://localhost:3100',
  clientId: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_CLIENT_ID ?? '',
  clientSecret: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_CLIENT_SECRET ?? '',
  hostedApplication: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_HOSTED_APPLICATION ?? 'zoltasoft-starter',
  callbackUrl: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_CALLBACK_URL ?? 'http://localhost:3300/api/identity/starter/auth/callback',
  sessionCookie: 'zolta-starter-identity-session',
  defaultRedirect: '/dashboard',
  logoutRedirect: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_LOGOUT_REDIRECT ?? '/'
}

const saasIdentityApplication = {
  mode: 'hosted',
  identityApiUrl: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_IDENTITY_API_URL ?? process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_IDENTITY_API_URL ?? 'http://localhost:8100',
  hostedAuthUrl: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_HOSTED_AUTH_URL ?? process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_HOSTED_AUTH_URL ?? 'http://localhost:3100',
  clientId: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_CLIENT_ID ?? process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_CLIENT_ID ?? '',
  clientSecret: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_CLIENT_SECRET ?? process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_CLIENT_SECRET ?? '',
  hostedApplication: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_HOSTED_APPLICATION ?? 'zoltasoft-saas',
  callbackUrl: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_CALLBACK_URL ?? 'http://localhost:3300/api/identity/saas/auth/callback',
  sessionCookie: 'zolta-saas-identity-session',
  defaultRedirect: '/saas/dashboard',
  logoutRedirect: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_SAAS_LOGOUT_REDIRECT ?? '/'
}

const projectsIdentityApplication = {
  mode: 'hosted',
  identityApiUrl: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_PROJECTS_IDENTITY_API_URL ?? 'http://localhost:8201',
  hostedAuthUrl: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_PROJECTS_HOSTED_AUTH_URL ?? 'http://localhost:3201',
  clientId: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_PROJECTS_CLIENT_ID ?? '',
  clientSecret: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_PROJECTS_CLIENT_SECRET ?? '',
  hostedApplication: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_PROJECTS_HOSTED_APPLICATION ?? 'tasks-demo',
  callbackUrl: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_PROJECTS_CALLBACK_URL ?? 'http://localhost:3300/api/identity/projects/auth/callback',
  sessionCookie: 'zolta-projects-identity-session',
  defaultRedirect: '/projects/dashboard',
  logoutRedirect: process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_PROJECTS_LOGOUT_REDIRECT ?? '/projects'
}

const viteAllowedHosts = [
  'localhost',
  '127.0.0.1',
  'dev-starter.talredservices.com',
  ...(process.env.NUXT_VITE_ALLOWED_HOSTS ?? '')
    .split(',')
    .map(host => host.trim())
    .filter(Boolean)
]

export default defineNuxtConfig({
  nitro: {
    rollupConfig: {
      plugins: [{
        name: 'starter-runtime-dependency-boundary',
        generateBundle(_options, bundle) {
          if (process.env.NODE_ENV === 'development') return
          const excludedPackages = ['braces', 'listhen', 'node-forge']
          const chunks = Object.values(bundle).filter(output => output.type === 'chunk')
          const modules = chunks.flatMap(chunk => Object.keys(chunk.modules))
          const references = [...modules, ...chunks.flatMap(chunk => chunk.imports)]
          for (const reference of references) {
            const normalized = reference.replaceAll('\\', '/').replaceAll('\0', '').split('?')[0] ?? ''
            if (excludedPackages.some(name => normalized === name || normalized.includes(`/node_modules/${name}/`) || normalized.startsWith(`${name}/`))) {
              this.error(`Build tooling must not enter the Starter runtime: ${reference}`)
            }
          }
          this.emitFile({
            type: 'asset',
            fileName: 'starter-runtime-dependencies.json',
            source: JSON.stringify({ version: 1, moduleCount: modules.length, excludedPackages })
          })
        }
      }]
    }
  },
  extends: [
    './layers/dashboard',
    './layers/saas',
    './layers/projects',
    './packages/i18n',
    './packages/nuxt-content',
    './packages/ui'
  ],
  icon: {
    clientBundle: {
      scan: true,
      icons: [
        'lucide:orbit',
        'lucide:layers-3',
        'lucide:route',
        'lucide:sparkles',
        'lucide:house',
        'lucide:kanban-square',
        'lucide:check-circle-2',
        'lucide:shield-check',
        'lucide:layout-dashboard',
        'lucide:list-checks',
        'lucide:folder-kanban',
        'lucide:log-out',
        'lucide:plus',
        'lucide:arrow-right',
        'lucide:arrow-up-right',
        'lucide:file-text',
        'simple-icons:php',
        'simple-icons:laravel',
        'simple-icons:nuxt',
        'simple-icons:typescript',
        'simple-icons:mysql',
        'simple-icons:docker',
        'simple-icons:github'
      ]
    }
  },
  modules: [
    '@nuxt/eslint',
    '@vueuse/nuxt',
    'nuxt-auth-utils',
    'nuxt-csurf',
    '@talred/identity-consumer-nuxt'
  ],
  app: {
    pageTransition: {
      name: 'talred-page',
      mode: 'out-in'
    }
  },
  site: {
    url: process.env.ZOLTA_STARTER_SITE_URL ?? 'http://localhost:3300'
  },
  runtimeConfig: {
    public: {
      siteUrl: process.env.ZOLTA_STARTER_SITE_URL ?? 'http://localhost:3300',
      zoltasoftSiteUrl: process.env.NUXT_PUBLIC_ZOLTASOFT_SITE_URL
        ?? (process.env.NODE_ENV === 'development' ? 'http://localhost:3000/en' : 'https://dev-app.talredservices.com/en'),
      identityAuthUrl: process.env.NUXT_PUBLIC_IDENTITY_AUTH_URL ?? process.env.NUXT_TALRED_IDENTITY_APPLICATIONS_STARTER_HOSTED_AUTH_URL ?? 'http://localhost:3201',
      docsUrl: process.env.NUXT_PUBLIC_DOCS_URL
        ?? (process.env.NODE_ENV === 'development' ? 'http://localhost:3200' : 'https://dev-docs.talredservices.com')
    },
    talredIdentity: {
      sessionSecret: process.env.NUXT_TALRED_IDENTITY_SESSION_SECRET ?? '',
      applications: {
        'starter': starterIdentityApplication,
        'zoltasoft-starter': starterIdentityApplication,
        'starter-demo': starterIdentityApplication,
        'saas': saasIdentityApplication,
        'zoltasoft-saas': saasIdentityApplication,
        'projects': projectsIdentityApplication,
        'tasks-demo': projectsIdentityApplication
      }
    }
  },
  i18n: {
    locales: [
      { code: 'en', name: 'English', file: 'en.json' },
      { code: 'fr', name: 'Français', file: 'fr.json' }
    ],
    defaultLocale: 'en',
    langDir: 'locales'
  },
  routeRules: ({
    '/__nuxt_content/**': { csurf: false }
  } as any),
  vite: {
    server: {
      allowedHosts: viteAllowedHosts
    }
  },
  compatibilityDate: '2025-07-15',
  devtools: { enabled: process.env.NUXT_DEVTOOLS === 'true' },
  eslint: {
    config: {
      stylistic: { indent: 2, quotes: 'single', semi: false, commaDangle: 'never', braceStyle: '1tbs' }
    }
  }
})
