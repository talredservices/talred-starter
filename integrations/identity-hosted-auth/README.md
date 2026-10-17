# Identity hosted authentication integration

This directory owns this repository's hosted-auth presentation assets and
configuration. The Identity project remains a generic authentication service;
it reads the hosted application's name, page-set slug, and validated appearance
from its own hosted-application record.

`hosted-applications.json` is the presentation source of truth for this
repository's examples. The local initializer synchronizes its page-set and
appearance values through Identity's hosted-application API. Identity only
renders a custom page set when the selected host has compiled that layer and
enabled the slug; otherwise it falls back to its generic auth pages.

The extracted page layers live in `page-sets/`. `nuxt.config.ts` is the single
integration entry point for compiling them into a selected Identity host. A
generic Identity build does not include this integration. A consumer-specific
runtime must explicitly mount or package this directory, extend this config,
and enable matching page-set slugs in both the Identity API and Nuxt host.
Set `IDENTITY_HOSTED_AUTH_PAGE_SETS` for the API and the Nuxt build default;
an already-built Nuxt host can override its comma-separated runtime allowlist
with `NUXT_IDENTITY_HOSTED_AUTH_PAGE_SETS`.

Keep application names, themes, logos, and custom auth shells here. Do not add
consumer keys, brands, or page implementations to the Identity source tree.
