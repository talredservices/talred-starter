# Codex skills

The Starter is the source of truth for Talred-specific Codex skills. They live at `.agents/skills/` so a clone exposes the skills alongside the architecture docs and setup tools.

The local set covers Talred delivery, API and client architecture, Identity hosted auth, independent themes, and improvement workflow. Generic Laravel, Nuxt, Tailwind, Stripe, Cloudflare, design, testing, and deployment skills belong in the developer's global skill directory. The Starter does not install or prescribe a global dependency list.

## Install into a larger workspace

From this repository, preview copying the local set to the parent workspace:

```bash
python3 scripts/manage-codex-skills.py install --target .. --dry-run
```

Then omit `--dry-run` to copy missing skills. The command is idempotent. Identical copies are left alone; differing destination files are reported as conflicts and never overwritten. Run `check` to confirm that the target copies match this Starter:

```bash
python3 scripts/manage-codex-skills.py check --target ..
```

The default target is the Starter itself, where the source skills are already available. The script only copies the Talred skill set and catalog. It does not install or modify global skills.

## Naming and compatibility

Use `talred-*` for Talred-owned delivery and architecture skills. Keep `identity-hosted-auth-pages` because it describes the Identity integration boundary. Zolta package names, `.zolta` configuration paths, runtime service IDs, and `@zoltasoft/*` package identifiers remain unchanged where they are part of a technical contract.
