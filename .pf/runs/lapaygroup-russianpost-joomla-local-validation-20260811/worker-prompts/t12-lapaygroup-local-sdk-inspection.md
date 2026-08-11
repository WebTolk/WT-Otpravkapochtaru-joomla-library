# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t12-lapaygroup-local-sdk-inspection`
- run_id: `lapaygroup-russianpost-joomla-local-validation-20260811`
- mode: read-only product code, scratch writes allowed

## Goal

Re-check `lapaygroup/russianpost` 2.0.0 using the local source copy already
placed under `.pf/tmp/LapayGroup-RussianPost-2.0.0/RussianPost-2.0.0`.

Do not try to download the SDK from Composer or GitHub. The local copy is the
source under test.

## Required Checks

- Confirm local SDK metadata from its `composer.json`:
  - package name
  - PHP constraint
  - required extensions
  - PSR-4 namespace
- Confirm `src/Http/Psr18Transport.php` exists and inspect its constructor
  contract.
- Build a temporary scratch bootstrap that:
  - loads Joomla vendor autoload from `joomla.local`
  - registers the local SDK PSR-4 namespace from `.pf/tmp/LapayGroup-RussianPost-2.0.0/RussianPost-2.0.0/src`
  - uses `Joomla\Http\Http` as PSR-18 client
  - uses `Laminas\Diactoros\RequestFactory`
  - uses `Laminas\Diactoros\StreamFactory`
  - uses `Laminas\Diactoros\UploadedFileFactory`
  - attempts to instantiate `LapayGroup\RussianPost\Http\Psr18Transport`
- If constructor arguments differ from the assumed factories, report the exact
  signature and the smallest Joomla-way adapter needed.
- Do not make live Russian Post API calls in this task unless a harmless local
  request can be performed without credentials.

## Allowed Writes

- `.pf/tmp/lapaygroup-local-sdk-inspection/**`
- `.pf/artifacts/worker-lapaygroup-local-sdk-inspection-20260811.md`

## Forbidden Writes

- product source files
- Joomla extension manifests
- installer scripts
- release package config
- `joomla.local` product/runtime files outside scratch temp

## Output

Write `.pf/artifacts/worker-lapaygroup-local-sdk-inspection-20260811.md` with:

- commands and scratch file locations
- PASS/FAIL per check
- constructor signature for `Psr18Transport`
- class-instantiation result
- whether this resolves the previous T07/T08 Composer-install blocker
- remaining blockers for a real migration proof

Do not print secrets.
