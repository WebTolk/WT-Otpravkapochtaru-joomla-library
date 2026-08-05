# WebTolk To ProcessForge Migration Log

- Timestamp: 2026-08-05T19:40:19+04:00
- Agent/role: Codex / migration operator
- Project root: D:\Dev\WT-Otpravkapochtaru-joomla-library
- Product code policy: product code was not edited.
- Old flow folder: `.webtolk`
- Old flow backup: `.webtolk.backup-20260805-193132`
- New ProcessForge folder: `.pf`

## Worktree Before Migration

- Git status: clean `main...origin/main`.
- Existing `.pf`: missing.
- Existing `.webtolk`: present.
- Existing project `AGENTS.md`: missing.
- Existing `.webtolk/AGENTS.md`: present and loaded.

## Inventory Summary

Initial `.webtolk` inventory:

- Root files: `AGENTS.md`, `README.md`.
- Durable flow areas: `config`, `context`, `docs`, `evolutions`, `extensions`, `logs`, `patches`, `rules`, `skills`, `templates`.
- `.webtolk` file count before backup: 1174 files.
- `.webtolk/tmp`: 737 files, mostly generated/runtime/cache data.

Initial `.pf` inventory:

- `.pf` did not exist before onboarding.

## Migration Actions

- Wrote pre-migration plan to `.webtolk/logs/webtolk-to-processforge-pre-migration-plan.md`.
- Created full backup `.webtolk.backup-20260805-193132`.
- Ran ProcessForge onboarding dry-run with project type `joomla-extension`; result: safe brownfield plan.
- Ran ProcessForge onboarding apply; `.pf` was written, then post-check reported serviceable project-local issues.
- Added private ProcessForge entries to `.gitignore`.
- Added project-local `codex-filesystem` capability provider in `.pf/registries/tools.yaml`.
- Preserved an explicit capability waiver in `.pf/artifacts/capability-waivers.yaml` because `doctor-project` reads the onboarding resource report as well as the current snapshot.
- Generated/updated `.pf/START_AGENT_HERE.md`.
- Moved active package config from old flow storage to `.dist/build/package.config.json`.
- Updated `phing.xml` to use `.dist/build/package.config.json`.
- Updated QA cache paths from `.webtolk/tmp` to `.pf/runtime/cache`.
- Added `.pf` excludes to style/PHPCS configuration.
- Added a README note that the active project flow folder is now `.pf` and `.webtolk` is legacy/backup until manual cleanup.

## Changed Files

- `.gitignore`
- `.php-cs-fixer.dist.php`
- `.dist/build/package.config.json`
- `.pf/AGENTS.md`
- `.pf/START_AGENT_HERE.md`
- `.pf/artifacts/*`
- `.pf/assignments/first-assignment.yaml`
- `.pf/contexts/*`
- `.pf/handoffs/project-ready-handoff.md`
- `.pf/hooks.yaml`
- `.pf/logs/webtolk-to-processforge-migration.md`
- `.pf/logs/webtolk-to-processforge-verification.md`
- `.pf/packages/project.wt-otpravkapochtaru-joomla-library.yaml`
- `.pf/parameters.yaml`
- `.pf/process-forge.yaml`
- `.pf/registries/project-classifiers.yaml`
- `.pf/registries/tools.yaml`
- `.pf/reviews/project-init-review.md`
- `.pf/reviews/project-onboarding-review.md`
- `README.md`
- `phing.xml`
- `phpcs.xml`
- `phpstan.neon`

Ignored/private generated files:

- `.pf/process-forge.local.yaml`
- `.pf/runtime/`

## Legacy Artifacts Carried Forward

Copied to `.pf/artifacts/legacy-webtolk-migration/`:

- `webtolk-flow-core/`: old flow instructions/config/context/logs/patches/rules/skills/templates/evolutions/extensions.
- `webtolk-docs-selected/briefs/`: 1 brief.
- `webtolk-docs-selected/reports/`: 28 reports.
- `webtolk-tmp-selected/swjprojects-publication-docs-20260711/`: 7 local publication files.
- `webtolk-tmp-selected/order-tracking-check-20260725/`: 10 runtime assurance files.
- `webtolk-tmp-selected/rest-api-capture-20260711-http-summary.json`: 1 compact summary.
- Total selected legacy files after removing copied generated product build snapshots: 144 files, 759315 bytes.

Left only in backup/live legacy folder:

- Full `.webtolk/tmp/dot-tmp/`.
- Full `.webtolk/tmp/phpstan/`.
- Full `.webtolk/tmp/php-cs-fixer/`.
- Full `.webtolk/tmp/verify/`.
- `.webtolk/tmp/screenshots/`.
- `.webtolk/tmp/legacy-agents-build/`.
- Large raw HTML/API capture trees not selected for active `.pf/artifacts`.

## Link Decisions

Replaced active flow/tooling references:

- `phing.xml`: `.webtolk/build/package.config.json` -> `.dist/build/package.config.json`.
- `.php-cs-fixer.dist.php`: cache path `.webtolk/tmp/php-cs-fixer/...` -> `.pf/runtime/cache/php-cs-fixer/...`; added `.pf` exclude.
- `phpstan.neon`: `tmpDir` moved from `.webtolk/tmp/phpstan` to `.pf/runtime/cache/phpstan`.
- `phpcs.xml`: added `.pf` exclude while preserving `.webtolk` legacy exclude.
- `README.md`: added short active-flow note.

Preserved:

- Global `D:/.agents/...` references, because they point to shared infrastructure.
- `.webtolk` references inside backup and copied legacy evidence, because they are historical records.
- `.webtolk` ignore/exclude references that intentionally keep legacy flow out of package/QA scans.

## Risks And Follow-Up

- `.webtolk` remains in place until explicit operator approval for deletion.
- `doctor-project` still reports a warning for explicit runtime-access waiver, while `project-context-check` is fresh and continues after the project-local registry provider was added.
- The shared old Joomla platform contract path `D:\.agents\docs\joomla-toolkit\...` was not found, while ProcessForge workplace resource checks found `docs.joomla-toolkit`; this did not block migration.
