# Tasks dashboard template

The Projects layer combines a real Laravel-backed Tasks example with isolated SaaS presentation fixtures. The feature transformation is recorded in `docs/tasks-dashboard-transformation.json`.

## Routes and data

- `/projects/dashboard`: searchable project cards with active/archived filtering, name/task-count sorting, local pagination, direct links to each project board, and a project-delete action.
- `/projects/dashboard/tasks?project=<uuid>`: existing project/task APIs, filtered status/completion metrics, paginated sortable table, task details slideover, and existing board. Summaries explicitly describe matching tasks when filters are active.
- `DELETE /api/projects/{projectId}`: authenticated, owner-scoped project removal. The existing database foreign key permanently cascades deletion to associated tasks.
- `/projects/dashboard/billing`: static demonstration plan, storage usage, and invoice table. It performs no billing request or payment action.
- Overview activity: static, explicitly labeled demonstration events; it is not an audit log.

## Dashboard data states

- `/projects/dashboard` loads the authenticated project collection and presents projects as responsive cards. Cards show the project key, optional description, status, and task count, and open the matching board. Search, active/archived filtering, task-count or name sorting, and local pagination remain available. Project creation is available from the overview and Tasks page, including after the first project exists. Owners can delete a project from either its card or task board after confirming that all its tasks will be permanently removed.
- With no projects, the overview shows the create-project empty state. If search or filters match no projects, the card grid shows a separate no-results state. Tasks are shown and managed on the project board, not previewed on the overview.
- A project with no tasks shows the empty board state while retaining the task form, filters, metrics, and table/board switch. The overview never creates a task implicitly.
- Project deletion is owner-scoped at the API boundary. The confirmation modal discloses the permanent task cascade; cancel/dismiss performs no request, while success removes the card or returns the board to the overview. Failures leave the project visible and allow retry.
- Failed collection reads show an error and retry action instead of presenting a failed request as an empty workspace. A missing or inaccessible project ID has a recovery route back to project selection.
- Task deletion uses the same confirmation modal from both table and board views. The selected task remains until deletion succeeds; failures are shown in the modal and can be retried.
- The Tasks route auto-opens the only available project, shows a project picker for multiple projects, and offers first-project creation when the collection is empty. Both dashboard pages retain explicit loading, empty, failure, and success feedback.

All routes retain the existing Projects Identity session and BFF authorization. Browser writes retain CSRF headers and existing server schemas; API contracts and Laravel domain behavior are unchanged.

## Reuse and ownership

- `useProjectsOverview` owns project query/create state, schema validation, error/retry and duplicate-submit locks. `useProjectDeletion` owns the confirmed deletion request and pending/error feedback.
- `useProjectsTasksPage` owns task reads/writes, debounced search, stale-read protection, action locks and feedback.
- `ProjectsPortfolioTable` renders the responsive project-card grid and local search/filter/sort/pagination controls; `ProjectTasksTable` composes the task table and view controls.
- `TasksMetricCards` and `TasksTablePagination` are reusable feature components; pagination has explicit EN/FR accessible labels.
- `useTasksDashboardPresentation` owns EN/FR dashboard copy.
- `useTasksDemoData` owns billing/activity fixtures independently of API-backed state. Replace this composable with a properly authorized server read model when adding real billing or activity. Do not treat these fixtures as payment or audit evidence.
- The shared dashboard shell receives actual command-search groups and readable mobile/search dialog labels. The layer's documentation navigation points to configured Docs rather than the Starter homepage.

Installed Nuxt UI v4 component source/types informed table slots, pagination slots, search props, progress props, and sidebar menu titles. No dependency was added. No team/assignee panel, historical task chart or due-date metric is introduced: those are outside the current task contract and requested scope.

## Verification boundaries

Use focused ESLint and the declared `pnpm run lint:types` through `local-dev-ops/scripts/with-stack-paused`. Review authenticated EN/FR desktop/mobile routes against actual project data. Do not create or delete a user's task merely to exercise this presentation example. A production bundle is not needed for this component/composable-only slice when the declared typecheck and live renderer pass; deployments still use the project build gate.

## Verification record

- 2026-10-14T04:26:25Z | Target: `talred-starter/apps/web/layers/projects` | Actor: Codex session | Action: dashboard data-state audit, task title patch typing correction, and invalid sidebar icon correction | Method: authenticated browser review plus focused ESLint, `pnpm run lint:types` through `with-stack-paused`, and `git diff --check` | Services: shared local Compose stack; wrapper restored all previously running services | Artifacts: Projects overview/tasks pages, dashboard layout, create-project modal, project portfolio, task table, task page composable, and this reference | Evidence: overview, project selection, account settings, and empty project table/board rendered as expected; project preview/navigation, empty metrics, task filters, view switch, and create-project entry points were available; ESLint, typecheck, and diff check passed; fresh browser tab had no console errors or hydration mismatch. Confirmed `globe` exists and `globe-2` does not in the installed icon set.
- The test account now includes the previously authorized `Dashboard audit project` (`dashboard-audit-20261014`). No tasks were created or deleted. Task create/status/delete submission was intentionally skipped to preserve the user's empty board. The detail title update path now includes the API-supported `title` field and is typechecked. The original browser tab remained on its connection-error page after the stack restart; the original project board was restored in a new tab.
- 2026-10-14T04:32:02Z | Target: `talred-starter/apps/web/layers/projects` | Actor: Codex session | Action: simplified Projects overview and changed project portfolio to responsive cards | Method: authenticated browser review, focused ESLint, locale JSON parse, and `git diff --check` | Services: existing local web runtime | Artifacts: Projects overview, project-card component, EN/FR locale copy, and this reference | Evidence: browser showed the smaller page-title icon, no task preview or portfolio heading, and two clickable project cards with filters and pagination; console warning/error log was empty; all listed local checks passed. Typecheck and automated tests were not run.
- 2026-10-14T04:41:02Z | Target: `talred-starter/apps/api` and `apps/web/layers/projects` | Actor: Codex session | Action: implemented confirmed owner-scoped project deletion with task cascade | Method: DDD transformation validation, focused ESLint, PHP syntax checks, EN/FR and transformation JSON parsing, Zolta command/route map generation, API route listing, and `git diff --check` | Services: local PHP CLI and existing local web runtime | Artifacts: DeleteProject command/API/BFF/client flow, confirmation modal on dashboard cards and project board, locale copy, and `docs/tasks-project-deletion-transformation.json` | Evidence: transformation validator reported `valid: true`; command map rebuilt; route list showed `DELETE api/projects/{projectId}`; all focused static checks passed. Typecheck wrapper refused to run because Docker API inspection was unavailable; browser inspection timed out, so the modal was not visually verified. No project or task was deleted. Automated tests were not run.
