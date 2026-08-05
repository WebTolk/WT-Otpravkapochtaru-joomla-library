# WebTolk To ProcessForge Pre-Migration Plan

- Timestamp: 2026-08-05T19:31:04+04:00
- Agent/role: Codex / migration operator
- Project root: D:\Dev\WT-Otpravkapochtaru-joomla-library
- Source flow folder: .webtolk
- Target flow folder: .pf
- Product code policy: product code and extension source files are out of scope and must not be edited.

## Initial State

- Git status before migration: clean `main...origin/main`.
- Existing `.pf`: not present.
- Existing `.webtolk`: present.
- ProcessForge launcher: `D:\.agents\processforge\bin\pf.py`.
- ProcessForge workplace: `D:\.agents\processforge-workplace`.
- ProcessForge version observed before migration: 1.0.2.

## Migration Scope

Move the project process layer from the old project-local `.webtolk` flow to a ProcessForge `.pf` project layer while preserving legacy history and evidence.

## To Preserve In Backup

- Full `.webtolk` tree, including config, context, rules, skills, logs, patches, reports, docs, generated dumps, runtime caches, screenshots, verifier scripts, and temporary files.
- Existing `.webtolk/tmp` runtime/cache output will remain available in backup and will not be copied into active `.pf/runtime`.

## To Carry Into `.pf`

- Legacy process evidence under `.pf/artifacts/legacy-webtolk-migration/`.
- Migration log under `.pf/logs/webtolk-to-processforge-migration.md`.
- Verification log under `.pf/logs/webtolk-to-processforge-verification.md`.
- Legacy context/config/rules/logs/patches/docs that are useful as historical evidence, separated from active ProcessForge runtime files.

## Link Update Policy

- Update only project-local process-flow references where `.webtolk` means the active project flow.
- Preserve global `D:\.agents\...` references because they identify shared infrastructure.
- Keep `.webtolk` references in backup and legacy evidence unchanged for historical accuracy.
- Do not modify product code. Repository QA/build config references will be reviewed separately and only changed if they are process-flow metadata, not product behavior.

## Known Risks And Manual Decisions

- `.webtolk/tmp` is large and contains generated/cache data; copying it wholesale into active `.pf` would pollute ProcessForge runtime.
- The Joomla platform contract points to `D:\.agents\docs\joomla-toolkit\...`, but this machine currently has `D:\.agents\docs\joomla\...`; this is a shared documentation routing gap, not a blocker for process migration.
- Deletion of `.webtolk` is explicitly out of scope until operator confirmation after successful verification.
