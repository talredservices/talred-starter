# Hosted auth implementation reference

Replace every placeholder from the selected client's own source. The names
`APPLICATION_KEY`, `PAGE_SET`, `PRODUCT_NAME`, and `PRODUCT_THEME` are notation,
not literal configuration. This reference is based on a working Identity-hosted
flow, but does not carry a previous application's name or palette forward.

## Locate the existing seams

Within the selected Identity host, inspect:

```text
layers/auth/default-pages/app/pages/auth/
layers/auth/default-pages/app/layouts/identity-auth.vue
layers/auth/default-pages/app/components/IdentityAuthFormHeader.vue
layers/auth/default-pages/app/middleware/identity-live-auth.ts
layers/auth/default-pages/app/utils/identity-auth-page.ts
layers/auth/server/api/hosted-auth/entry.get.ts
layers/auth/server/utils/identity-hosted-auth.ts
nuxt.config.ts
```

In the consumer, inspect the installed Identity consumer's public BFF contract,
its application configuration, and the product's CSS/brand assets. In the
runtime repository, find the canonical Compose or deployment profile and the
owning idempotent application sync. A separately installed Identity host may
have a different path and configuration; inspect that host directly.

## Expected route sequence

```text
consumer /auth/login
  -> consumer /api/identity/<route-key>/auth/authorize
  -> Identity /auth/login?application=APPLICATION_KEY&state=...&intent=...
  -> Identity /api/hosted-auth/entry
  -> Identity /auth/PAGE_SET/login?application=APPLICATION_KEY&state=...
  -> existing Identity form/server operation
  -> consumer BFF callback
```

The entry route validates the intent and uses the hosted application record's
`auth_page_set` only if the Identity host enables that slug. The generic
`/auth/login` is the fallback. The consumer route key, hosted application key,
Identity project slug, and page-set slug need not be identical; resolve each
from its own configuration.

## Application and appearance shape

Use the installed Identity management service or idempotent sync to bind the
correct project and BFF client. This is schema notation, not executable PHP:

```text
key: APPLICATION_KEY
name: PRODUCT_NAME
project_id: target Identity project
primary_client_id: target confidential BFF client
application_url: consumer origin
callback_url: consumer BFF callback
auth_page_set: PAGE_SET
status: active
appearance:
  background_preset: identity
  accent_color: PRODUCT_THEME.primary_action
  welcome_text: null
  design_tokens:
    font_family: PRODUCT_THEME.font
    light_background: PRODUCT_THEME.light.background
    light_background_muted: PRODUCT_THEME.light.muted_background
    light_card: PRODUCT_THEME.light.card
    light_text: PRODUCT_THEME.light.text
    light_muted: PRODUCT_THEME.light.muted_text
    light_border: PRODUCT_THEME.light.border
    light_input_border: PRODUCT_THEME.light.input_border
    light_status: PRODUCT_THEME.light.status
    dark_background: PRODUCT_THEME.dark.background
    dark_background_muted: PRODUCT_THEME.dark.muted_background
    dark_card: PRODUCT_THEME.dark.card
    dark_text: PRODUCT_THEME.dark.text
    dark_muted: PRODUCT_THEME.dark.muted_text
    dark_border: PRODUCT_THEME.dark.border
    dark_input_border: PRODUCT_THEME.dark.input_border
    dark_status: PRODUCT_THEME.dark.status
    primary_50 ... primary_950: PRODUCT_THEME.primary_ramp
```

Use the target website's actual six-digit color values and the key/value
formats accepted by its Identity API. If the client has a logo, upload it via
Identity's branding mechanism and confirm the public metadata returns its
`logoUrl`. A null `logoUrl` means the generic icon fallback is being shown;
do not describe that as the client's logo.

The public `GET /api/hosted-auth/application?application=APPLICATION_KEY`
response should expose the expected `authPageSet`, name, appearance, and
sanitized `designTokens`. The shared layout maps token keys to
`--identity-hosted-*` variables and the primary ramp to Nuxt UI variables.
Check computed browser styles because a correct API payload can still be
misapplied by the layout.

## Compiled page set

The consumer-owned layer and route structure is:

```text
integrations/identity-hosted-auth/
  nuxt.config.ts
  hosted-applications.json
  page-sets/PAGE_SET/
  nuxt.config.ts
  app/components/ProductAuthShell.vue
  app/pages/auth/PAGE_SET/login.vue
  app/pages/auth/PAGE_SET/register.vue
  app/pages/auth/PAGE_SET/forgot-password.vue
  app/pages/auth/PAGE_SET/reset-password.vue
  app/pages/auth/PAGE_SET/verify-email.vue
```

The consumer integration `nuxt.config.ts` aggregates its page sets. A selected
Identity build includes that aggregator through
`IDENTITY_HOSTED_AUTH_INTEGRATIONS`; each intended API and Nuxt host enables
the slug in `IDENTITY_HOSTED_AUTH_PAGE_SETS=default,PAGE_SET`. A built Nuxt
host can override its comma-separated runtime list with
`NUXT_IDENTITY_HOSTED_AUTH_PAGE_SETS`. A generic Identity build includes no
consumer layer.
The shell should be a page-set marker and generic page renderer:

```vue
<script setup lang="ts">
import type { Component } from 'vue'

defineProps<{ page: Component }>()
provide('identity-auth-page-set', 'PAGE_SET')
</script>

<template>
  <component :is="page" />
</template>
```

The concrete login route imports the existing generic login screen:

```vue
<script setup lang="ts">
import DefaultLoginPage from '#layers/auth/default-pages/app/pages/auth/login.vue'

definePageMeta({ layout: 'identity-auth', middleware: 'identity-live-auth' })
</script>

<template>
  <ProductAuthShell :page="DefaultLoginPage" />
</template>
```

Use the same pattern for the other screens. The generic screen retains its
server calls, validation, CSRF handling, and hosted navigation. The page-path
helper can read `identity-auth-page-set` from the shell when the concrete
route has no `pageSet` parameter. Do not add a second form implementation or
unrequested content outside the form.

## Password-reset and fallback seams

The hosted password-reset notification resolves the requesting client and
generates `/auth/PAGE_SET/reset-password` for a valid named set. It uses
`/auth/reset-password` for default or invalid values. Test the generated link;
checking only the login route misses this seam. Verify register, forgot,
reset, and verify links retain the hosted application and page namespace.

The default page set remains compiled and enabled. A disabled or unknown
custom set resolves to generic routes. Do not treat the mere existence of
`/auth/PAGE_SET/login` as proof that the hosted entry selects it.

## Verification shape

Use the consumer's login entry with one browser context so cookies survive
redirects. Verify the final Identity path, metadata, and rendered form at
desktop and mobile widths. A Playwright assertion can use discovered values:

```ts
const expectedPath = `/auth/${pageSet}/login`
await page.goto(consumerLoginUrl)
await expect.poll(() => new URL(page.url()).pathname).toBe(expectedPath)
await expect(page.locator('.identity-auth-form-shell')).toBeVisible()
const sizes = await page.evaluate(() => ({
  scroll: document.documentElement.scrollWidth,
  client: document.documentElement.clientWidth
}))
expect(sizes.scroll).toBe(sizes.client)
```

Also inspect the form header for `PRODUCT_NAME`, the actual uploaded logo or
explicit fallback, computed light/dark CSS variables, browser errors, and
reset/registration/verification navigation. Test a default or disabled set
through hosted entry. A direct request to the final Identity URL alone does
not verify BFF authorization or page-set selection.

After source changes, use only the selected target's declared verification
profile. After persisted metadata changes, rerun its owning idempotent sync;
after layer/env changes, restart only the affected Identity Nuxt host if the
runtime requires it. Inspect before restarting a healthy Compose service.
