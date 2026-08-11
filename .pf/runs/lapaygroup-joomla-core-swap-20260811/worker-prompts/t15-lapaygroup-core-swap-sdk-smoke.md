# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t15-lapaygroup-core-swap-sdk-smoke`
- run_id: `lapaygroup-joomla-core-swap-20260811`
- mode: read-only runtime smoke after T14

## Goal

After T14 registers the SDK in Joomla core/vendor, run read-only smoke checks
through `lapaygroup/russianpost` 2.0.0 using existing system plugin parameters.

## Required Checks

- Confirm the SDK autoloads from Joomla vendor without manual scratch mapping.
- Read system plugin parameters from Joomla configuration/database. Do not print
  raw secrets; report only key presence and lengths.
- Build the LapayGroup `Psr18Transport` with:
  - `Joomla\Http\Http`
  - `Laminas\Diactoros\RequestFactory`
  - `Laminas\Diactoros\StreamFactory`
  - `Laminas\Diactoros\UploadedFileFactory`
- Instantiate the relevant LapayGroup API facade/provider with the plugin
  credentials. If constructor names differ, inspect local SDK source and report
  the exact construction.
- Run only read-only calls:
  - settings/account info
  - shipping points
  - one postoffice lookup
  - one tariff calculation
- Do not create, edit, delete or return orders.
- Sanitize all outputs to shape, counts and allowlisted non-secret keys only.

## Allowed Writes

- `.pf/tmp/lapaygroup-core-swap-sdk-smoke/**`
- `.pf/artifacts/worker-lapaygroup-core-swap-sdk-smoke-20260811.md`

## Forbidden Writes

- product source files
- Joomla stand library/vendor/plugin files
- Russian Post account mutations

## Output

Write `.pf/artifacts/worker-lapaygroup-core-swap-sdk-smoke-20260811.md` with
commands, PASS/FAIL per call, sanitized evidence and blockers.
