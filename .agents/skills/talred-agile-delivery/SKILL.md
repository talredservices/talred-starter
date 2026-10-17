---
name: talred-agile-delivery
description: "Orchestrate TalredServices feature prompts as pragmatic Agile iterations: brainstorm, plan, create and synchronize Linear work, complete DDD and user-facing design, define a bounded vertical slice, implement, verify, and close the iteration. Use for new ecosystem features; do not impose ceremony on questions or trivial fixes."
---

# Talred Agile Delivery

Treat each new feature as a small iteration with a visible beginning, boundary, and done state. Use the smallest process that preserves shared understanding and a verifiable outcome.

## Feature-prompt intake

For every prompt that requests a new capability, feature, workflow, page, service, integration, or meaningful behavior change, automatically run this intake before implementation:

1. Classify the request as a feature, bug fix, maintenance change, review, or question. Only feature requests enter the full intake; explain the lighter path for the other categories.
2. Brainstorm the feature in context: user problem, desired outcome, actors, constraints, possible approaches, recommended approach, assumptions, risks, dependencies, and a smallest valuable release. Do not brainstorm disconnected ideas or silently expand the request.
3. Convert the recommendation into a feature plan containing the user story, DDD transformation scope, design scope, acceptance criteria, non-goals, implementation tasks, Definition of Done, and verification plan.
4. Synchronize the plan to Linear before coding. Search first and reuse an existing matching issue. Otherwise create one parent feature issue, add user-story/implementation sub-issues only where they are independently verifiable, assign the appropriate team and owner, and place the work in an existing active cycle. Create a new cycle only when no suitable cycle exists and the connected workspace permits it. If the connector is unavailable, stop before implementation; a local plan is not authorization to mutate code.
5. Start execution from the first bounded sub-issue, keeping the parent issue as the source of truth. Update status and comments as work moves through discovery, design, implementation, review, blocked, and done.

Do not create a Linear project for every feature. Use a project only when the feature is a multi-iteration product initiative or the user explicitly asks for one. Do not create speculative tickets for rejected brainstorm options, unresolved blocking decisions, or work outside the agreed slice.

If the Linear connector is unavailable, produce the same plan locally, clearly mark synchronization as blocked, and do not implement or claim that tickets, cycles, or statuses were created.

## Linear as the work system

When the Linear connector is available, use the connected Linear workspace as the system of record for the iteration:

- discover the workspace, current user, team, workflow states, and active cycles before creating or changing work;
- locate an existing issue before creating a duplicate;
- use one parent feature issue as the iteration anchor; use sub-issues for user stories or implementation tasks only when they have an independently verifiable outcome;
- treat Linear cycles as sprints and choose an existing active cycle before creating a new one;
- represent the shaped backlog item as a Linear issue, keeping the user outcome and DDD transformation linked in the description or attachment;
- put the sprint slice, non-goals, design decisions, acceptance criteria, and Definition of Done on the issue or its clearly related sub-issues;
- keep Linear status, assignee, cycle, and comments synchronized with actual implementation progress;
- close the issue only after the verification and handoff checks pass; record follow-up work as separate backlog items.
- when acting as a coordinator, fetch a fresh parent/child issue snapshot immediately before selecting or dispatching work; stale, missing, cyclic, active, duplicate, completed and blocked work must never be dispatched.
- before any live verification, resolve runtime ownership from the repository and local-dev-ops docs. For the TalredServices Nuxt workspace, resolve `local-dev-ops/local/compose.yaml` from the active workspace root as the canonical managed stack. Inspect its README and compose service, run the documented Docker readiness flow, and test the container-served application. Never start a competing host Nuxt dev server or assume ports 3000/3100 are free; inspect `docker compose ps`, service health, logs, and port ownership first.
- on a fresh Codex session, inspect resumable coordinator state and use the newest user instruction to choose continuation, new scoped work, or an instant response; do not use a fixed keyword or force unrelated prompts into the automation loop.
- route every user prompt through `zolta_control_plane_entrypoint` first. It must inspect live Codex sessions, select only approved skills from the control-plane catalog, choose direct handling or bounded delegation, fan out only independent Linear issues within policy, and receive worker reports before replanning. Workers never become coordinators or dispatch successors.
- treat every `zolta run` work or continuation request as coordinator-owned until proven otherwise: collect the complete live Codex session snapshot and fresh Linear parent/child graph, ask the orchestrator to decide direct handling versus parallel independent workers, and do not implement or dispatch from the user session before a bounded plan exists. The orchestrator owns worker reports, fresh replanning, Linear sequencing, and Slack `#codex` closeout.
- create one dedicated coordinator Codex thread before dispatching any implementation worker. Persist its thread ID with the orchestration run and post only material transitions—plan, dispatch, completion, blocker, adapter failure, Linear change, recovery, and final status—while remaining quiet when state is unchanged. Workers report to this coordinator and never replace it.
- if continuation discovers an active or queued Linear implementation slice, the requesting session is read-only until the coordinator thread exists: it may load `.zolta`, inspect repositories and collect host/Linear context, but must not implement, run implementation verification, choose completion, or dispatch workers itself.
- after every native host action, record the actual outcome through `zolta_control_plane_host_event` with a stable idempotency key. Pending coordinator, worker, Linear, Slack, runtime, or successor actions are not completion evidence until the host bridge acknowledges them.
- use the entrypoint's durable `.zolta` bootstrap context to orient fresh sessions from the canonical manifest, runtime profile, feature plans, open problems, and improvement proposals. This is orientation evidence, not current truth: always refresh live Codex sessions and Linear before selecting or dispatching work.
- after marking an issue complete, resolve the `#codex` Slack channel by name and send a concise completion report through the Slack plugin with the issue identifier, outcome, verification, known limitations, and follow-ups. Treat channel discovery and delivery as part of closeout; if Slack is unavailable, record the notification as unsent rather than fabricating a channel ID or claiming delivery.
- after the completion report, inspect the parent issue’s remaining dependency graph. If unfinished work remains, dispatch exactly one new Codex session/task for the next unblocked Linear issue in sequence and pass the completed issue, report, verification evidence, limitations, and remaining work. Instruct that successor to coordinate additional agents for later independently verifiable slices, keep Linear and `#codex` synchronized, and repeat only while work remains. Do not dispatch duplicates or blocked issues; if session dispatch is unavailable, record the continuation as blocked and stop without claiming it was started.

Use the native Linear connector for authenticated workspace mutations. The local `zolta-control-plane` MCP validates TalredServices workflow artifacts and must not store or proxy Linear credentials. If Linear is unavailable, do not silently create a parallel source of truth: prepare the iteration contract locally and report that Linear synchronization remains pending.

## Iteration workflow

1. **Shape the backlog item.** State the user/problem, business outcome, actor, scope, and why it matters. Split unrelated outcomes into separate items.
2. **Transform the domain.** Invoke `$talred-feature-ddd` as the mandatory first implementation phase. Identify the bounded context, use cases, rules, ownership boundaries, and acceptance criteria. Validate the transformation before writing feature code. Link it to the Linear issue when Linear is available.
3. **Design the experience.** For user-facing work, invoke `$web-ui-ux-design` first to define the user path, content architecture, visual hierarchy, interaction states, accessibility, responsive behavior, and success signal. Then use `$nuxtui-components` for component/composition decisions and `$nuxtui-theme` for tokens, themes, variants, and scoped overrides. Use `$nuxt-ui` for version-specific Nuxt UI APIs when needed, and `$web-design-guidelines` for final UI/accessibility review. Record the design decision and unresolved usability risks on the Linear issue. For non-UI work, record `design: not-applicable` and why.
4. **Plan the sprint slice.** Select one thin vertical slice that can be completed and verified. Record in-scope behavior, explicit non-goals, dependencies, risks, design decisions, and the Definition of Done. Do not expand scope silently.
5. **Implement collaboratively.** Enter this phase only when the DDD transformation, design decision, sprint scope, non-goals, and acceptance criteria are explicit. Route the work to the narrowest Talred skill, inspect the nearest pattern, and build the smallest end-to-end slice. Preserve the TalredServices boundaries between domain, API, BFF, client, Identity, runtime, and generated applications.
6. **Review and verify.** Check the implementation against the domain contract, design decisions, acceptance criteria, and Definition of Done. Run focused tests and the relevant lint/type/build/live checks. For UI work, include keyboard, responsive, accessibility, and visual-state review. Treat failed checks as feedback requiring correction or a documented scope decision.
7. **Close the iteration.** Report what changed, what was verified, known limitations, and any follow-up backlog items. If a failure or repeated pattern produced a durable lesson, record it through `zolta ... propose` and update only the owning skill when justified.

## Working rules

- Keep user stories outcome-oriented; implementation tasks are supporting work, not the feature itself.
- Prefer acceptance criteria that describe observable behavior, authorization, error semantics, and affected boundaries.
- For user-facing features, include observable design acceptance criteria: task clarity, primary action, loading/empty/error/success states, keyboard operation, responsive behavior, and accessibility.
- Use explicit `in-scope` and `out-of-scope` statements to protect the sprint slice.
- Re-plan when the domain model, ownership, or acceptance criteria materially change; do not continue under a stale plan.
- A feature is done only when its agreed behavior is implemented, verified, and handed off with limitations visible.
- For trivial presentation or maintenance changes, use a lightweight iteration note and state why full domain discovery is unnecessary.

## Implementation phase contract

### Skill routing

| Work shape | Primary skill | Implementation responsibility |
|---|---|---|
| Laravel/Zolta domain, API, persistence, or authorization | `$talred-api` | Domain invariants, application use cases, trusted identity, routes, resources, repositories, migrations, and API verification |
| Nuxt starter client or BFF | `$talred-client` | Server-mediated transport, upstream mapping, browser-safe state, CSRF, localization, cache scope, and client verification |
| Feature crossing API, BFF, client, Identity, or runtime | `$talred-api` + `$talred-client` | End-to-end ownership map, cross-stack contract, and vertical-slice verification |
| User-facing Nuxt UI | `$nuxtui-components`, `$nuxtui-theme` | Semantic component composition, states, tokens, variants, responsive behavior, and accessible interaction |
| UI review or accessibility audit | `$web-design-guidelines`, `$web-ui-ux-design` | Review against the design intent, task flow, usability, accessibility, and responsive criteria |

Use supporting skills together when a feature crosses boundaries; do not ask one skill to absorb another skill’s ownership. For example, a cross-stack Talred feature may route its API slice to `$talred-api` and its BFF/client slice to `$talred-client`, while this delivery skill coordinates the contract.

### Managed runtime selection

The control-plane runtime profile is authoritative for live verification. For the TalredServices Nuxt workspace, it points to `local-dev-ops/local/compose.yaml`, service `zoltasoft-nuxt`, and `http://localhost:3000`. Before Playwright, inspect `local-dev-ops/local/README.md` and execute the profile's preflight: compose services, running services, container status/ports, and host port ownership. Reuse healthy services; inspect logs and perform only targeted restarts for unhealthy/exited services. Build or recreate only when preflight shows stale images/configuration or unrecoverable readiness. Then run the managed compose readiness command and use the existing container. Do not switch to an unmanaged host `pnpm dev` process. Record runtime failures separately from implementation.

### Execution order

1. Read the target repository contract, package scripts, runtime configuration, and nearest complete pattern.
2. Map each acceptance criterion to its owning layer and verification method.
3. Implement the domain/application behavior and persistence boundary first when business state changes.
4. Implement the API contract with trusted actor/tenant/project context and explicit error semantics.
5. Implement the BFF/browser boundary with validation, authentication, normalization, CSRF-safe mutations, and scoped caching where relevant.
6. Implement client state and UI using the approved design decisions, localized copy, semantic tokens, and all required interaction states.
7. Add focused tests at each changed seam before broad verification.

Do not skip a layer by moving business rules into Vue pages, accepting authority from the browser, exposing upstream envelopes, or coupling the client directly to Laravel/Identity/third-party services.

### Exit criteria

Implementation may move to Review and Verify only when:

- every in-scope acceptance criterion has an implementation location and a check;
- the owning skills’ boundary and security rules are satisfied;
- the smallest complete vertical slice works through its real runtime seams;
- tests cover the changed domain/API/BFF/client behavior proportionally;
- generated routes/maps/configuration are refreshed where required;
- skipped checks, known limitations, and follow-up backlog items are recorded on the Linear issue.

## Handoff

### Orchestration self-improvement loop

The control plane must distinguish a product failure from a failure of its own coordination system. For a dispatch, coordinator-thread, host-adapter, Linear/Slack, managed-runtime reconciliation, or verification-evidence failure caused by orchestration, record an idempotent host event with `systemFailure: true`. The orchestrator then creates at most one `codex.improvement_worker` action for that event, requiring `talred-improve`. The improvement agent reads the durable `.zolta` run journal and canonical manifest, diagnoses the narrowest owning seam, records a `zolta ... propose` improvement, validates any change, and reports to the coordinator. It must not modify product work or dispatch successors. The coordinator re-tests the improvement and only then resumes the normal tester/repair/worker sequence; repeated distinct failures may create new improvement actions, but the loop must never duplicate an active action or spin without new evidence.

Before dispatching coordinated work, create or reuse one host-managed `codex.resource_monitor` session. It must periodically report CPU, memory, disk, and authenticated five-hour/weekly token usage through `zolta_control_plane_resource_monitor`. The orchestrator pauses new dispatches at critical pressure (90% or higher) and waits at elevated pressure (80% or higher), resuming only after a later healthy observation. Routine browser verification, tests, container inspection, and targeted restart of an unhealthy service are default-allowed; destructive data loss, secret access, production mutation, irreversible deletion, and privilege changes remain approval-gated.

The monitor may propose a bounded `codex.resource_cleanup` action for inactive duplicate VS Code processes, stale browsers without active verification, or stale non-coordinator Codex sessions. The host executes only candidates explicitly marked orphan/stale/inactive, never active coordinator/worker/tester sessions, production processes, or user data, and records the real result. Cleanup recommendations must remain idempotent, reversible, and capped per heartbeat.

Each monitor heartbeat emits one bounded `slack.resource_health_report` action to `#codex` every 30 seconds containing health metrics, five-hour/weekly token usage, the decision, remarks, and cleanup candidates. Delivery is host-acknowledged; an unsent or unknown Slack tool must be reported as unsent rather than inferred as delivered.

The coordinator watchdog checks worker/tester leases every 30 seconds. Each action must heartbeat; a tester that misses its bounded lease is marked expired, its stuck host session is recovered, and one replacement is dispatched. After the bounded retry budget, escalate the host/session failure to `codex.improvement_worker` and keep unrelated independent work eligible. Never infer success from a hung session or create unlimited replacements.

Use four independent workers by default and never more than six. Fill the available parallelism with non-overlapping eligible issues when the resource monitor is healthy; reduce or pause dispatch when it reports elevated or critical pressure. Dependency safety, active-session detection, and verification gates always take precedence over throughput.

### Linear cache and synchronization

Fetch the parent/child graph once when a coordination run starts and persist its normalized form in the run’s `.zolta/coordination/<coordinationId>/linear-cache.json`. All dependency analysis, issue selection, and local status mutations use that cache; do not poll Linear for every worker decision. At each bounded completion checkpoint, emit one idempotent `linear.cache_sync` action for the host adapter to apply to live Linear and acknowledge with an actual host event. Refresh the cache only on explicit coordinator request, expiry, conflict, or evidence that external Linear changed; never silently replace local coordination state with a new live snapshot.

The post-sync graph is authoritative for the next loop iteration. Import any issues created during the run into the existing cache, then sequence them through the same dependency, resource, verification, reporting, and closeout gates. Do not declare the coordination run complete merely because the initial snapshot was exhausted.

After DDD and design phases, route implementation to the narrowest owning skill: `$talred-api` and `$talred-client` for cross-stack slices, `$talred-api` for Talred APIs, `$talred-client` for Talred clients/BFFs, and the relevant product or operations skill for other work.
