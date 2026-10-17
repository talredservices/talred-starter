---
name: talred-themes
description: "Design and generate isolated, versioned UI themes for projects and hosted-application screens, including semantic tokens, layouts, components, and licensed assets. Use when a project needs an independent visual identity or when generated auth pages must differ per project; do not use for generic UI styling without project isolation or for changing authentication semantics."
---

# Talred Independent Themes

Use this skill to create a project's visual system as a deliberate, isolated output. The platform supplies contracts, security, lifecycle, and extension points; the generated project or selected theme package owns visual values, assets, and presentation. Do not solve independent branding by adding application-key conditionals to shared Identity code.

## Decide the theme boundary first

Before writing UI, establish what is independent:

- the complete generated customer app;
- hosted Identity auth screens only;
- a subset of auth screens such as login and registration;
- a reusable product/template theme used by several generated apps.

Capture the required visual direction, target devices, locales, accessibility level, reduced-motion behavior, asset licensing, light/dark modes, and the screens/states that must match. State what remains platform-shared, especially authentication behavior, authorization, security messaging, transport, and error semantics.

## Define a versioned theme contract

Create a typed, versioned manifest before scattering values through components. Prefer semantic tokens over raw values:

- color roles: surface, text, muted, border, primary, focus, error, success, warning;
- typography: family, weights, sizes, line heights, letter spacing;
- layout: spacing scale, container widths, breakpoints, density;
- shape and elevation: radii, borders, shadows;
- interaction: focus rings, hover/pressed/disabled states, motion preferences;
- assets: logo, favicon, fonts, illustrations, icons, and legal attribution where required.

Keep token names stable and use a schema/default version. Validate token values and asset types at the generation/configuration boundary. Do not allow arbitrary CSS, executable code, or untrusted filesystem paths to enter Identity rendering.

## Generate at the owning boundary

Choose the least shared implementation that satisfies the requirement:

- Generated app theme: write the manifest, CSS variables/AppConfig, components, and assets into the generated project or a versioned theme package.
- Hosted auth theme: select a compiled Identity page-set/theme adapter and pass validated theme metadata or safe asset URLs through the hosted application contract. The page set owns screen structure; the hosted application owns its selected theme values.
- Reusable theme: package it with an explicit compatibility/version contract and make it selectable by generation configuration.

Prefer one theme adapter with token injection over duplicating every auth page. Add a new page-set adapter only when structure or interaction genuinely differs. Runtime selection must use validated identifiers and allowlisted compiled implementations; never dynamically import a customer path.

## Preserve project isolation

Namespace generated assets, CSS variables, storage paths, caches, and preview output by project/hosted application. Ensure a project with no custom theme receives the documented default. Verify that switching between two projects cannot retain the previous theme through SSR data, browser state, cache keys, or static assets.

Keep these boundaries intact:

- theme cannot change authentication, authorization, callback/state validation, CSRF, session handling, or security-critical copy;
- theme cannot expose secrets, raw tokens, internal paths, or customer data;
- hosted metadata is validated and cache scope includes the application identity;
- uploaded assets are type/size/extension validated, safely stored, and served with appropriate cache and content policies;
- generated assets are reproducible and licensed for the intended product.

## Complete every screen and state

For hosted auth, cover login, registration, forgot password, reset password, email verification, expired/invalid handoff, unavailable configuration, validation errors, loading, disabled, and recovery states. Preserve the authorization query contract and keep navigation inside the selected page set.

For generated customer apps, cover first-run setup, authenticated shell, empty/error/loading states, responsive layout, keyboard navigation, localization, contrast, focus visibility, reduced motion, and asset failure. Use existing Nuxt UI and Talred Starter conventions where they do not conflict with the selected theme contract.

## Verification protocol

Generate or render at least two projects with materially different themes from a clean source. Verify:

1. Their server-rendered auth screens expose the correct theme and logo before hydration.
2. Their CSS variables, assets, cache keys, and page-set routes remain isolated.
3. Every required screen and state uses the same theme contract.
4. Missing or invalid theme data falls back safely without breaking auth.
5. Mobile, keyboard, contrast, localization, reduced motion, and network/asset failures remain usable.
6. Build, lint, typecheck, route generation, and the real Playwright/browser flow pass using the repository's pinned tools.

Add a schema/contract fixture, generated-output assertion, visual snapshot, or end-to-end test that would catch cross-project leakage and stale generated assets. Verify the served container or preview, not only source files, when mounts, caches, or generated output can be stale.

## Theme evolution and handoff

Record the theme ID, schema/page-set versions, source of truth, generated files/assets, compatibility fallback, and verification evidence. When a theme changes, migrate existing manifests or provide a backward-compatible default. Do not silently alter another project's theme.

A theme is complete only when the requested surfaces are independently themed, all required states are covered, and the generated result can be reproduced. Report explicitly which surfaces are theme-owned and which remain platform-owned.
