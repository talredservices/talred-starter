---
name: identity-hosted-auth-pages
description: Implement or repair client-branded Identity-hosted login, registration, recovery, reset, and verification pages for a hosted application. Use when a Talred Identity project client needs its own auth page flow and theme while retaining the generic fallback.
metadata:
  short-description: Build client-branded Identity-hosted auth pages
---

# Identity-hosted auth pages

Use the selected Identity host and consumer repositories. A hosted application
record supplies its appearance metadata and may select a safe page-set slug.
The Identity host renders its generic auth pages by default; optional
consumer-owned layers are included only by an explicitly configured host. The
consumer's BFF keeps authorization intent, callback, session, and secrets. Do
not add consumer names, keys, or brands to Identity source, build a new auth
protocol, or put Identity credentials in browser code.

For code shapes and a verification example, read
[the implementation reference](references/implementation-reference.md) when
creating a page set or tracing a broken flow. Its names are placeholders.

## Discover this client's contract

1. Follow the workspace's target-selection and runtime instructions. Inspect
   the Identity host, consumer BFF configuration, installed consumer package,
   hosted-application record or idempotent sync, and actual local/production
   runtime declarations. Do not inherit a different Identity installation.
2. Record the project's slug, hosted application key, confidential BFF client,
   callback URL, optional page-set slug, product display name, brand asset, and
   website light/dark palette. Keep consumer-owned values in its integration
   bundle.
3. Trace one complete generic auth screen and the hosted entry resolver before
   editing. Reuse their server-mediated operations and navigation helpers.
4. Inspect the product's CSS tokens and logo source. Never reuse another
   client's name, logo, slug, icon, or palette as a starting value.

## Preserve the routing boundary

The consumer's `/auth/login` starts an authorization intent and redirects to
Identity with `application`, `state`, and `intent`. Identity's middleware sends
that request through `/api/hosted-auth/entry`; the entry validates the intent,
resolves the hosted application, and selects `/auth/<page-set>/login` only when
the persisted `auth_page_set` is in the host's enabled page-set list. Default,
missing, invalid, or disabled selections use `/auth/login`.

Keep query parameters needed by each screen (`application`, `state`, `email`,
`token`) and return through the existing consumer BFF callback. Do not bypass
the entry handler by changing only the consumer URL. Check recovery notification
URLs separately: a hosted reset link must retain the selected page set and
default to `/auth/reset-password` when no valid set is selected.

## Configure the application and brand

Use the Identity project's owning management API or idempotent sync. The
hosted-application record must have an active key, the correct project and
`primary_client_id`, the consumer application/callback URLs, and a valid
`auth_page_set`. Set `appearance` from that product's source of truth:

- `accent_color` and `design_tokens.primary_50` through `primary_950` from its
  actual primary ramp;
- `font_family`, light/dark background, muted background, card, text, muted
  text, border, input border, and status surface tokens from the website;
- a hosted logo through Identity's supported upload/storage path when the
  client has a real logo. `logo_url` is the render-time source. If absent, the
  shared neutral icon is an intentional fallback, not proof of a branded logo;
- `welcome_text: null` for a form-only composition unless extra copy is
  explicitly requested.

Use token keys and value formats accepted by the installed Identity API. The
shared auth layout loads public hosted metadata and maps its sanitized tokens
to auth and Nuxt UI CSS variables; it should also render the hosted name/logo.
Do not hardcode a client palette, product name, or logo in shared components.

## Build only the page-specific layer needed

Create a named Nuxt layer under the consumer's
`integrations/identity-hosted-auth/page-sets/<page-set>/`. Use that bundle's
`nuxt.config.ts` as the single layer aggregator. The selected Identity host
must explicitly include the integration with
`IDENTITY_HOSTED_AUTH_INTEGRATIONS` and enable the same slug in
`IDENTITY_HOSTED_AUTH_PAGE_SETS`; keep `default` enabled. Set the API and
Nuxt build defaults with that variable; a deployed Nuxt host can override its
runtime allowlist with `NUXT_IDENTITY_HOSTED_AUTH_PAGE_SETS`. Database
selection, host compilation, and the host allowlist are all required. A
generic Identity release includes none of the consumer integration layers.

Put concrete pages at `app/pages/auth/<page-set>/<screen>.vue` for login,
register, forgot-password, reset-password, and verify-email. Each page imports
its matching generic screen, keeps the existing auth layout and middleware,
and renders it through a thin page-set shell. The shell provides
`identity-auth-page-set` and renders the generic screen only. Shared links use
the existing page-path helper or equivalent route context so the namespace
survives movement between screens. Avoid duplicating form validation, CSRF,
Google, password reset, verification, session exchange, or callback code.

The default page is a centered auth form. Do not add a story panel, benefits
list, marketing copy, or split layout without an explicit design request.
Apply the hosted brand to the form and existing layout. For optional dynamic
icons, include their identifiers in the host's explicit Nuxt Icon client
bundle; use every configured locale for new user-visible copy.

## Verify the live behavior

Follow the selected target's verification profile and inspect the canonical
runtime before restarting it. After changing persisted metadata, run only its
own idempotent sync. After changing the Nuxt layer or enabled set, recreate only
the affected Identity console when required. Do not start a parallel host
server for a Compose-owned target.

From the consumer login route, verify the redirect through hosted entry to
`/auth/<page-set>/login`, then inspect the public hosted metadata and rendered
form. At desktop and mobile widths, check the product name, configured logo or
explicit fallback, computed accent and light/dark tokens, visible form,
page-set links, no overlap or horizontal overflow, and no browser errors.
Exercise registration, recovery, reset, and verification route continuity.
Also verify the generic fallback with a default or disabled page set.

Run the target's relevant lint, type/build, backend, syntax, and diff checks.
Report any unavailable runtime or check accurately; source inspection alone
does not prove a hosted flow works.
