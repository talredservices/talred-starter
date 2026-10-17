---
name: talred-improve
description: "Continuously improve the customer-app delivery workflow by turning verified failures, friction, repeated manual work, UX gaps into safe, tested, reusable improvements across skills, CLIs, scripts, generators, APIs, BFFs, Identity, and documentation. Use during feature work, generated-app workflows, automation changes, verification failures, theme generation, or post-incident follow-up; use alongside domain-specific skills, not as a replacement."
---

# Talred Improvement

Use this skill as the ecosystem's continuous-improvement loop and meta-workflow. The goal is that every meaningful discovery makes the next agent run more reliable, efficient, complete, and easier to verify—without turning one-off preferences into noisy global rules. The local taxonomy is maintained in [skills/catalog.md](../catalog.md). Treat that taxonomy as adaptive as responsibilities move and new boundaries become visible.

## Scope and activation

Apply this skill when any of these is observed:

- a failed, flaky, slow, or confusing command, test, deployment, browser flow, or agent workflow;
- a repeated manual step that a CLI, script, generator, fixture, validator, or documented procedure could safely remove;
- a mismatch between source, generated customer app, runtime configuration, container, or deployed behavior;
- a missing error, loading, empty, responsive, accessibility, localization, or recovery state in a customer-facing flow;
- a missing contract, stale skill, unclear runbook, or undocumented version-specific convention;
- a completion gap discovered while building a feature, especially one that would recur for future hosted apps.

Do not use it to justify unrelated cleanup, broad rewrites, production changes, or automatic edits to every skill after a single anecdote. A lesson becomes reusable only after its scope and evidence are clear.

## Operating loop

For each improvement, follow this loop:

1. **Observe.** Capture the exact trigger: user report, command, URL, log line, screenshot, failing assertion, timing, or missing expected artifact. Reproduce the smallest reliable case when possible.
2. **Locate the owner.** Trace the behavior to its source of truth: skill or runbook, CLI/script, generator/template, API/domain, Nuxt BFF/client, Identity, bootstrap/configuration, test harness, or runtime container. Fix the owning boundary rather than patching each consumer.
3. **State the hypothesis.** Describe the failure cause, the smallest safe change, and how success will be measured. Separate corrective, preventive, efficiency, and customer-experience improvements.
4. **Implement narrowly.** Prefer deterministic, idempotent, rerunnable, resumable automation with dry-run or validation modes. Preserve existing public contracts unless a versioned migration and compatibility path are part of the task.
5. **Add a regression guard.** Add the cheapest durable proof that would catch recurrence: a unit/integration/E2E test, contract fixture, architecture check, CLI self-check, generated-output snapshot, health check, or documented verification command.
6. **Verify the real path.** Run the focused check, then the relevant lint/type/test/build/route/cache/container/Playwright flow. Verify the served runtime when source mounts, caches, or generated artifacts can hide stale code. Record skipped checks and the reason.
7. **Persist and teach.** Update the narrowest reusable skill, architecture topic, runbook, CLI help, fixture, or failure-prevention note. Include the evidence and a concrete example. If the lesson is local to one feature, keep it local; do not promote it to a universal rule.
8. **Run the skill-impact review.** Before handoff, inspect the skills used by the task and the relevant local Talred skills. If a durable lesson belongs in one of them, teach it there in the same change and validate the edited skill.

Use the project architecture to choose the layer that should improve:

- Laravel/Zolta API: request/trusted context, DTO/use case, domain invariant, persistence, migrations, routes, resources, and generated route/CQRS maps.
- Nuxt client/BFF: browser-facing contracts, authentication/session and CSRF boundaries, upstream clients, normalization, error translation, caching, and UI state.
- Identity: authorization, hosted-app registration/configuration, authentication handoff, redirect/state contracts, and Identity-owned pages.
- Templates/generators/bootstrap: the source that creates customer-app files, environment configuration, seed data, routes, assets, or default UX. A generated-app defect usually belongs here before its generated output.
- Agent skills/CLI/scripts/docs: repeatable decision-making, safe orchestration, diagnostics, verification, and handoff. Keep secrets and product-specific customer data out of reusable guidance.

When behavior differs between source and a served app, compare the template, generated artifact, environment/bootstrap values, migrations, route/map caches, build output, and running container before changing application code.

For independent project or hosted-application theme generation, use the dedicated `talred-themes` skill. Keep this skill focused on discovering and teaching improvements to the owning workflow.

## Quality rules for automation

- Make safe operations idempotent and make destructive operations explicit, scoped, recoverable where practical, and confirmation-gated.
- Prefer a useful error with the failed step, expected state, safe remediation, and correlation/context identifiers already used by the system.
- Never log passwords, client secrets, access tokens, personal data, or complete callback state values. Use redacted identifiers.
- Add preflight checks before expensive or irreversible work: dependency/version checks, environment validation, service health, permissions, clean-generation checks, or dry runs.
- Keep generated apps reproducible from a clean checkout using pinned, supported tool versions. Do not silently depend on a developer's global packages or stale local build output.
- Automate verification of the customer experience, not only implementation details: first-run setup, authentication, redirects, authorization failures, loading/error/empty states, mobile layout, keyboard use, localization, and recovery.
- Prefer one authoritative implementation plus adapters over parallel copies. When a copy must exist for packaging or vendoring, document and verify synchronization.

## Improving skills and agent workflows

When an existing skill contributed to friction, first determine whether the issue is an incorrect rule, missing routing guidance, an absent verification step, or simply an implementation bug. Update the smallest relevant section and add a trigger/example that would have prevented the failure. Do not rewrite a skill for style alone, and do not weaken a safety rule to make a task pass.

Skills should tell the agent what to inspect, which source is authoritative, which choices are safe, how to verify, and what to report. They should not encode secrets, transient machine paths, unsupported assumptions, or a feature-specific workaround as a platform rule.

If an improvement needs a new CLI or script, define its interface and failure behavior first, then implement `--help`, validation, dry-run/idempotency behavior where applicable, actionable exit codes, and tests. If it needs agent automation, make the workflow observable and resumable so a later agent can continue from evidence rather than repeat guesses.

## Auto-teaching protocol for Talred skills

This skill is a meta-skill for maintaining the other Talred skills. Treat each completed task as a small teaching opportunity, not only as a code change. The agent must perform this review whenever it used a Talred skill, encountered a failure or repeated workaround, discovered a source-of-truth rule, or changed a workflow future agents will repeat.

1. **Inventory the lesson.** Ask: what did the agent need to know earlier, what wrong turn or missing check occurred, and would another agent likely encounter it again? Use concrete evidence from the task.
2. **Find the receiving skill.** Inspect the project-local `.agents/skills`, staged skills, and the skills explicitly used by the task. Prefer the domain skill that owns the decision (`talred-api`, `talred-client`, or a focused project skill). Teach `talred-improve` only when the lesson is about the improvement process itself.
3. **Write a teaching packet.** Add only the smallest guidance that changes future behavior: trigger/context, authoritative source, preferred decision or command, verification step, and a relevant anti-pattern. Use a short example when it prevents ambiguity.
4. **Choose the promotion level.** Keep a one-off detail in the feature or handoff. Put a recurring project convention in project guidance. Put a cross-project Talred workflow in the owning skill. Promote a lesson into multiple skills only when each skill genuinely needs a different application of it; link to one source instead of copying paragraphs.
5. **Validate the teacher.** Run the skill validator for edited skills. For a procedural change, perform a lightweight forward check using a realistic request or command: confirm the skill routes to the right owner, calls for the required verification, and does not authorize unrelated mutations.
6. **Update the taxonomy.** If a skill was created, renamed, or materially changed, update [skills/catalog.md](../catalog.md) with its primary category, subcategory, purpose, and related skills. Keep the catalog concise.
7. **Report the teaching.** Handoff must state which skill(s) were updated, the observed evidence, what future agent behavior changes, and whether the taxonomy changed. If no skill update is warranted, say why (for example, isolated bug, already documented, or insufficient evidence).

Skill updates are part of the feature's definition of done when the lesson is reusable. Do not silently edit unrelated global or installed skills; update the project-local source of truth unless the task explicitly promotes the lesson. Preserve existing metadata, invocation policy, safety boundaries, and user intent. Never weaken a skill's security or verification rule merely to make the current task easier.

## Evolving skill organization

Treat skill organization like a neuroplastic system: strengthen useful pathways, form new ones when responsibilities emerge, and retire or merge pathways that no longer fit. Before adding a rule or skill, check whether an existing skill should own it, be split, be merged, or change category.

Reorganize when evidence shows that a skill has multiple unrelated responsibilities, repeated cross-skill handoffs, unclear activation, duplicated guidance, or a changed platform boundary. Preserve discoverability during change: keep names descriptive, update the catalog and references in the same change, retain a short migration or alias note when renaming is unavoidable, and validate every affected skill. Do not reorganize for aesthetics alone.

When responsibilities evolve, prefer this sequence: observe repeated work, identify the new boundary, move or extract only the affected guidance, add routing links between related skills, run a realistic forward check, and record why the new shape is better. The taxonomy itself is a maintained platform artifact and may evolve as responsibilities change.

## Improvement record

Leave a concise record in the owning project documentation or handoff when the lesson is reusable:

```text
Trigger/evidence:
Observed behavior:
Root cause and owning boundary:
Change made:
Regression guard:
Verification performed (and skipped checks):
Remaining risk or follow-up:
```

## Completion standard

Do not call an improvement complete merely because the code change works locally. It is complete when the owning layer is corrected, recurrence has a guard, the relevant end-to-end path has been exercised or its blocker is explicit, and future agents have enough guidance to use the improved workflow. If external state prevents verification, add an offline contract check where possible and report the exact remaining uncertainty.
