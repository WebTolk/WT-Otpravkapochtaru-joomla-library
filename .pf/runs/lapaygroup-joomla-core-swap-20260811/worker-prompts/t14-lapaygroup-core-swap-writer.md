# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t14-lapaygroup-core-swap-writer`
- run_id: `lapaygroup-joomla-core-swap-20260811`
- mode: single writer for Joomla stand core/vendor files

## Goal

Directly register the local `lapaygroup/russianpost` 2.0.0 SDK inside the
`joomla.local` stand core/vendor/classloader area, without changing product
repository code.

## Required Work

- Read T13 artifact before writing.
- Backup every stand file/directory that will be touched into
  `.pf/tmp/lapaygroup-core-swap-backup-20260811`.
- Copy the local SDK source from
  `.pf/tmp/LapayGroup-RussianPost-2.0.0/RussianPost-2.0.0` into a stand vendor
  location suitable for Joomla's Composer autoloader.
- Register `LapayGroup\\RussianPost\\` in Joomla's Composer autoloader using a
  minimal reversible patch to stand core/vendor classloader files.
- Do not edit product repo source.
- Do not edit the installed Webtolk facade unless absolutely required for the
  classloader proof; if required, stop and report instead of changing it.
- After writing, run a Joomla-loaded PHP check proving:
  - `LapayGroup\RussianPost\Http\Psr18Transport` is autoloadable without manual
    scratch `addPsr4`
  - the class can be instantiated with `Joomla\Http\Http` and Laminas Diactoros
    factories

## Allowed Writes

- Joomla stand core/vendor files required for the SDK/classloader swap
- `.pf/tmp/lapaygroup-core-swap-backup-20260811/**`
- `.pf/tmp/lapaygroup-core-swap-writer/**`
- `.pf/artifacts/worker-lapaygroup-core-swap-writer-20260811.md`

## Forbidden Writes

- product source files
- extension source repo manifests and installer scripts
- release package config
- Russian Post account mutations

## Output

Write `.pf/artifacts/worker-lapaygroup-core-swap-writer-20260811.md` with:

- backed up files
- changed stand files
- exact classloader registration method
- proof command and result
- restore instructions
- blockers or deviations
