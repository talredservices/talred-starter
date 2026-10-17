# Talred skill catalog

This catalog organizes the Codex-first, Talred-specific skills shipped with the Starter. Keep skill folders directly under `.agents/skills/` so automatic discovery and explicit `$skill-name` invocation remain reliable. Generic framework, provider, design, and authoring skills belong in a developer's global skill directory; the Starter does not install a global dependency set.

## 1. Delivery and improvement

### 1.1 Cross-project workflows

- `talred-agile-delivery` — coordinates a Talred feature iteration and routes work to the owning skills.
- `talred-feature-ddd` — transforms business feature requests into a reviewable domain model and acceptance contract.
- `talred-improve` — teaches verified improvements into the owning skill, docs, script, or verification path.

## 2. Application architecture

### 2.1 API

- `talred-api` — Laravel/Zolta service boundaries, CQRS, DDD, persistence, events, and API verification.

### 2.2 Client and BFF

- `talred-client` — Nuxt feature layers, BFF routes, upstream clients, Identity/session boundaries, and client verification.

## 3. Identity and presentation

### 3.1 Hosted authentication

- `identity-hosted-auth-pages` — client-branded hosted Identity authentication pages and page-set continuity.

### 3.2 Independent themes

- `talred-themes` — isolated theme contracts and visual assets for generated applications and hosted auth.

## Classification rules

- Give every skill one primary category and one specific subcategory. Use related-skill links for cross-cutting concerns instead of duplicating its instructions.
- Keep directories flat; category organization belongs here and in the skill description/metadata.
- Keep generic technology guidance global. Keep Talred architecture, delivery, Identity integration, and theme contracts local to this Starter.
- When a skill is created, renamed, or materially changed, update this catalog and validate the affected skill in the same change.
- Do not add a global installer list here. Each developer curates their own global skills.
