# Reference feature layer

[← Reference feature layer index](index.md) · [↑ Nuxt architecture index](../index.md) · [Architecture home](../../api/index.md)

## Jobs layer as the primary structural example

The reference `jobs` layer demonstrates a rich feature without putting its behavior in pages. Treat it as a structural example; copy the separation of concerns, not its product vocabulary or external provider.

### Layer tree

```text
layers/jobs/
  app/
    pages/jobs/                 route views
    components/                 cards, grids, detail, search, forms
    composables/
      state.job.ts              shared reactive state and error registry
      query.job.ts              saved-job reads, pagination, selection
      query.job.search.ts       provider-search reads and stale-response guards
      actions.job.ts            saved-job writes and local updates
      actions.job.search.ts     search-result writes and synchronization
      client.job.ts             BFF transport and request-body mapping
      store.job.ts              readonly saved-job facade
      store.job.search.ts       readonly search facade
      model.job.ts              form/error/toast orchestration
      page.job.index.ts         search-page view model
      page.job.my.ts            saved-page view model
      inflight.job.ts           keyed mutation locks
      sync.job.search.ts        cross-store result synchronization
    utils/                      pure display/domain helpers
  server/
    api/jobs/                   BFF route handlers
    utils/
      job.service.ts            orchestration, enrichment, cache use
      job.client.service.ts     Laravel API client calls
      job.mapper.ts             upstream → client normalization
      job.schema.ts             BFF query/body schemas
      provider.service.ts       external provider integration
      provider.mapper.ts        provider → feature normalization
      job-cache-key.ts          deterministic cache keys
  shared/types/                 client/server feature contracts
```

### Client responsibility flow

`state` owns defaults, loading flags, pagination, selected item, and keyed errors. `query` owns reads, active query parameters, pagination, and stale-response protection. `actions` owns writes, in-flight locking, optimistic/local updates, and error capture. `client` owns only BFF calls and transport-body mapping. `store` exposes a readonly state facade plus explicit operations. `model` and `page` composables combine stores with route state, cookies, toasts, media queries, and lifecycle watchers.

A search can have two independent stores—provider results and saved user records—then synchronize them by stable external identity. A request sequence counter ensures an older response cannot overwrite a newer search. A keyed in-flight lock prevents duplicate save/update/delete operations for the same resource while allowing unrelated resources to mutate concurrently.

### Pages and components

Pages select layout/middleware, create the page model, coordinate route/query synchronization, and compose presentational components. They may own page-only concerns such as a selected tab, responsive drawer, or URL query. They should not build request bodies, call `$fetch` to upstream services, parse Laravel envelopes, or implement cache policy. Components render props and emit intent (`save`, `update`, `delete`, `select`, `loadMore`); they remain reusable and testable.

### BFF route and service flow

The jobs BFF separates each endpoint by HTTP method and resource (`index.get`, `index.post`, `[id].get`, `[id].put`, `[id].delete`, collection/summary routes, and provider-search routes). Routes validate schemas, require a session for protected operations, apply rate limits to expensive public searches, and delegate to feature services.

`job.client.service.ts` calls Laravel through the shared authenticated API client and translates `TalredApiError` into stable BFF errors. `job.mapper.ts` converts snake_case/server records into the feature’s client shape. `job.service.ts` owns orchestration: it can enrich a saved record from a cached provider record, filter saved records by external IDs, and coordinate multiple upstream calls. A provider service owns external search and provider-specific caching; it never leaks provider response shapes to the client.

### Merge and cache pattern

A provider-search BFF route may fetch public results, then—when a session exists—fetch the user’s saved records by external IDs and merge them into a request-local clone. Cache public provider results and individual normalized provider records with deterministic keys; never cache a session-enriched object under a public key. Invalidate feature-owned caches after writes, and scope user-sensitive keys by identity.

### Feature validation and mapping

Use Zod schemas for pagination bounds, enum states, optional fields, cross-field constraints, and normalized provider query flags. Use separate schemas for provider search, saved-list queries, create input, and update input. Map client forms into the BFF request body in `client` or a feature mapper, then map upstream records into the shared feature type in the BFF; each boundary owns its own naming and validation.
