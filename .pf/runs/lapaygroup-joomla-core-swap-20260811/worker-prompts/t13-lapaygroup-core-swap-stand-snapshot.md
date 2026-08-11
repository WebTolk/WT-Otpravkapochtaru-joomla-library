# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t13-lapaygroup-core-swap-stand-snapshot`
- run_id: `lapaygroup-joomla-core-swap-20260811`
- mode: read-only stand/product code, scratch writes allowed

## Goal

Inspect the `joomla.local` stand before a direct core/vendor SDK swap and write
a precise, reversible plan for the writer worker.

## Required Checks

- Locate current installed project library under the Joomla stand.
- Locate Joomla vendor autoload and Composer autoload map files.
- Locate whether `lapaygroup/russianpost` already exists in the stand vendor.
- Inspect local SDK source under `.pf/tmp/LapayGroup-RussianPost-2.0.0/RussianPost-2.0.0`.
- Confirm plugin params exist for the system plugin, but do not print secret
  values. Report only key presence, string lengths and whether values are empty.
- Identify OSPanel PHP executable that can bootstrap Joomla with database
  support.
- Produce the exact file list that T14 must backup before writing.

## Allowed Writes

- `.pf/tmp/lapaygroup-core-swap-stand-snapshot/**`
- `.pf/artifacts/worker-lapaygroup-core-swap-stand-snapshot-20260811.md`

## Forbidden Writes

- product source files
- Joomla stand library/vendor/plugin files
- Joomla extension manifests
- installer scripts
- release package config

## Output

Write `.pf/artifacts/worker-lapaygroup-core-swap-stand-snapshot-20260811.md`
with commands, PASS/FAIL per check, sanitized plugin parameter evidence and the
writer backup/patch plan.
