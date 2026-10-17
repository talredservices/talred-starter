# Client architecture

[← Client architecture index](index.md) · [↑ Nuxt architecture index](../index.md) · [Architecture home](../../api/index.md)

## Authentication and identity consumer

When the application uses `@talred/identity-consumer-nuxt`, keep hosted authentication and session handling in the auth layer/root session foundation. BFF routes use the server session helper and forward authenticated requests to upstream APIs. Client code consumes auth state and calls BFF routes; it does not store provider secrets or call the Identity Project directly.

For a custom identity provider, preserve the same boundary: a server session/auth helper resolves the principal, BFF routes enforce required permissions, and feature composables receive only the client-safe session state. Session expiry handling and CSRF protection are separate concerns.

## Client state and composable responsibilities

For a rich feature, use the jobs-style split:

```text
state.<feature>.ts    useState stores and default factories
query.<feature>.ts    reads, list/detail loading, state updates
actions.<feature>.ts  writes, locking, optimistic/local updates, errors
client.<feature>.ts   HTTP calls to /api/... only
store.<feature>.ts    readonly facade and derived state
model.<feature>.ts    domain-to-view composition
page.<feature>.ts     page-level orchestration
```

For simple CRUD/resource screens, an admin-style `useXxx` composable is acceptable. Keep pages thin: choose layout/middleware, obtain the page/model composable, and render components. Components emit intent or receive data/callbacks; they do not own upstream transport or cache policy.

## Nuxt ↔ Identity communication flow

When `@talred/identity-consumer-nuxt` is enabled, communication is server-mediated. The module registers no-store BFF endpoints for each configured application key:

```text
GET  /api/identity/<application>/auth/authorize
GET  /api/identity/<application>/auth/callback
GET  /api/identity/<application>/auth/session
POST /api/identity/<application>/auth/logout
GET  /api/identity/<application>/account/authorize
```

The login lifecycle is:

1. Browser calls `useTalredIdentity('<application>').login(returnTo, intent)` or navigates to the authorize endpoint.
2. Nuxt stores a short-lived state transaction in an encrypted, HTTP-only cookie and redirects to hosted Identity.
3. Identity authenticates/registers the user and returns a short-lived handoff code plus state to the Nuxt callback.
4. Nuxt validates state, exchanges the code server-side, and stores the encrypted consumer session; tokens do not enter the browser URL.
5. `useTalredIdentitySession().refresh()` calls the session endpoint and exposes only the public user profile to Vue.
6. A BFF route calls `requireIdentityUser(event, '<application>')` or `createIdentityApiClient(event, '<application>', options)` to obtain the current user/token and call the protected API.

```ts
// app/composables/useAuthenticationState.ts
export function useAuthenticationState() {
  const { csrf, headerName } = useCsrf()
  const identity = useTalredIdentity('starter', {
    defaultRedirect: '/dashboard',
    loginPath: '/auth/login',
    logoutHeaders: () => ({ [headerName]: csrf })
  })

  return {
    user: identity.user,
    ready: identity.ready,
    login: identity.login,
    logout: identity.logout,
    refresh: identity.refresh
  }
}
```

On the server, `createIdentityApiClient` attaches the current access token, refreshes once after an upstream 401, retries once, and clears the consumer session if refresh fails. A browser-side `useTalredIdentityFetch` can clear the session and begin login on 401; it must not turn 403 domain authorization failures into login redirects.

Configure a 32+ character session secret, hosted authentication URL, Identity API URL, confidential client credentials, registered callback URL, application key, cookie name, and local logout redirect in private runtime configuration. Use `SameSite=Lax`, `HttpOnly`, and `Secure` in production; reject unsafe external return paths.

## Authenticated mutations and CSRF

Every protected `POST`, `PUT`, `PATCH`, or `DELETE` uses the repository’s authenticated transport and CSRF convention:

```ts
const authenticatedFetch = useAuthenticatedFetch()
const { csrf, headerName } = useCsrf()

await authenticatedFetch('/api/tasks', {
  method: 'POST',
  headers: { [headerName]: csrf },
  body: input,
})
```

Do not use raw `$fetch` for authenticated mutations. Keep repeated transport in the feature client composable, handle session expiry centrally, and update/invalidate local state after the action completes.

## Resources, mapping, and data shape

BFF resources/mappers define client-facing types: singular resource, collection, pagination, empty state, acknowledgement, and error shapes. Normalize upstream data once in the BFF. The client should not know whether a field came from Laravel, an external provider, or a merged response. Use feature-shared types when both server and client need the same shape.

## Dashboard navigation and transitions

Nuxt 4.5.2 controls Vue page transitions (`pageTransition`) and browser view
transitions (`viewTransition`) independently. Disabling the former does not
disable the latter. The installed view-transition plugin starts a browser
transition in `router.beforeResolve` and completes its DOM update on
`page:finish`; its finish handler also clears shared transition state.

A directory that redirects after mount can start another transition before
the first finishes. In the verified local Chrome flow, that overlap cleared
the second transition's completion callback, leaving Chrome's visual update
blocked until its four-second timeout despite completed requests and rendering.
Use route metadata to opt shared dashboard shells out of browser transitions.
The global `transitions.global.ts` middleware centralizes this policy for the
`dashboard` layout; loading skeletons and authentication continue normally.
Use browser request and page-lifecycle timings to distinguish this visual
delay from network latency.

## Caching

Cache in the feature BFF service layer using root cache infrastructure. Build feature-local keys, scope user/session-sensitive data by the correct identity, cache normalized responses rather than raw upstream envelopes, and invalidate in the write service that owns the cached read. Never cache authorization-sensitive data under a global key.
