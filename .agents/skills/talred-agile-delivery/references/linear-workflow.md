# Linear synchronization contract

This reference applies only when the authenticated Linear connector is available in the current Codex session.

## Read before writing

Resolve the connected workspace, current user, target team, workflow statuses, and active cycle. Search by issue title and project before creating anything. Prefer the smallest field set supported by the connector to avoid overly complex queries.

## Feature-plan mapping

| Agile artifact | Linear representation |
|---|---|
| Feature plan | One parent feature issue |
| User story | Parent issue description or an independently verifiable sub-issue |
| Implementation task | Sub-issue with one observable outcome |
| Sprint | Existing active Linear cycle; create only when no suitable cycle exists |
| Product initiative | Linear project or initiative only when it spans multiple iterations |
| Acceptance criteria | Parent issue description and relevant sub-issue checklists |
| Decision/risk/blocker | Issue comment or structured description section |

Create the parent issue only after the agent has completed its internal brainstorm and has a recommended bounded slice. Record rejected alternatives only as concise decision context; do not create tickets for them.

## Issue shape

Use the issue description for:

1. user/problem and business outcome;
2. DDD transformation summary and link/path to the durable artifact;
3. user flow, content/hierarchy summary, and design artifact/link when user-facing;
4. in-scope behavior and explicit non-goals;
5. acceptance criteria, including UX/accessibility criteria when relevant;
6. Definition of Done;
7. dependencies, risks, and blocking assumptions.

Use sub-issues for independently verifiable implementation tasks, not for every file or command. Keep the parent issue open until the full slice is verified.

## Status discipline

Move status only to reflect actual work: backlog/triage, planned, in progress, review, blocked, or done according to the connected team's configured states. Add a concise comment when a review, verification result, scope change, blocker, or follow-up decision changes the iteration.

Never mark work done merely because code was written. The acceptance criteria and relevant Zolta verification checks must pass first.
