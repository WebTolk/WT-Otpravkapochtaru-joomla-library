# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t16-lapaygroup-core-swap-joomshopping-surface`
- run_id: `lapaygroup-joomla-core-swap-20260811`
- mode: read-only Joomla/JoomShopping surface check after T14

## Goal

Verify that the JoomShopping addon/admin form surface still works after the
Joomla core/vendor LapayGroup SDK registration.

## Required Checks

- Confirm the relevant system plugins are installed/enabled:
  - `wtotpravkapochtaru`
  - JoomShopping addon plugin that consumes the library
- Check Joomla logs/PHP error logs before and after the surface check.
- Exercise the JoomShopping addon configuration surface as far as possible from
  CLI or local HTTP/browser tooling available to the worker.
- Verify that Joomla Form fields from the installed Webtolk library still load
  and do not fatal after the core/vendor swap.
- Verify WebAsset field assets are still registered/usable if the form is
  rendered.
- Do not change plugin settings.
- Do not print raw credentials.

## Allowed Writes

- `.pf/tmp/lapaygroup-core-swap-joomshopping-surface/**`
- `.pf/artifacts/worker-lapaygroup-core-swap-joomshopping-surface-20260811.md`

## Forbidden Writes

- product source files
- Joomla stand library/vendor/plugin files
- Joomla plugin params
- Russian Post account mutations

## Output

Write `.pf/artifacts/worker-lapaygroup-core-swap-joomshopping-surface-20260811.md`
with commands, PASS/FAIL per surface, screenshots/log excerpts if available,
and blockers.
