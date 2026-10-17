# Talred Starter Web

`apps/web` is the Nuxt 4 client and BFF for Talred Starter. It includes a Starter workspace app and two runnable reference experiences: Talred Tasks and Fresh SaaS. The app keeps browser-facing API calls on same-origin Nuxt routes and keeps Identity client credentials on the server.

## Included experiences

- **Starter workspace** (`/` and `/dashboard`): create and manage workspaces, invite members, and link Identity projects. The `/identity` action opens the configured Talred Identity control plane.
- **Talred Tasks** (`/projects`): a task-management example with a Nuxt client/BFF, its own Identity application, and a Laravel API. Use it to trace a browser interaction through the API and domain layers.
- **Fresh SaaS** (`/saas`): a public-site and protected-dashboard example with its own Identity project and client. Its pricing page is structural sample content; no commercial plans or prices are configured.

The projects and SaaS examples have separate Identity sessions and data. The shared Identity source belongs to the canonical `identity` project; this app consumes it through the configured Identity integration.

## Setup

Run these commands from `apps/web`:

```bash
cp .env.example .env
pnpm install
pnpm dev
```

Configure the Identity application clients and API endpoints in `.env` when authenticated development is needed. Client secrets remain server-side; browser code talks to the Nuxt BFF. For the full local stack, follow the repository's developer setup and initialization documentation.

The included legal pages are review drafts. Before deploying a derived application, replace them with accurate publisher, contact, data-use, retention, and jurisdiction-specific information. This build uses first-party authentication cookies and browser-stored display preferences; it does not configure advertising or analytics trackers.

## Architecture

Shared UI, localization, and Nuxt Content packages live under `packages/`. The included experiences are organized as layers under `layers/`, with client presentation, BFF routes, and shared contracts kept at their appropriate boundaries. Read the [client architecture docs](../../docs/client/index.md), [full-stack contracts](../../docs/full-stack-contracts.md), and [Identity Project integration](../../docs/api/07-cross-cutting/identity-project-integration.md) before extending those boundaries.

## Commands

```bash
pnpm dev
pnpm lint
pnpm test
pnpm build
```

## Container

Build the Nuxt runtime from `apps/web`:

```bash
docker build -f docker/nuxt/Dockerfile -t talred-starter-web .
```

The web image contains the Nuxt server output. Laravel/API containers are defined and built from `apps/api` and `apps/platform-api`; browser requests reach them through same-origin BFF routes.

## License

Apache-2.0. The complete license is at the repository root.
