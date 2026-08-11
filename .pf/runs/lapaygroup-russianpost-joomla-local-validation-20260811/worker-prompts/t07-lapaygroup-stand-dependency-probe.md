# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t07-lapaygroup-stand-dependency-probe`
- run_id: `lapaygroup-russianpost-joomla-local-validation-20260811`
- mode: read-only product code, test-stand scratch writes allowed

## Goal

Verify whether `joomla.local` can host `lapaygroup/russianpost` 2.0.0 with
Joomla-way dependencies.

## Allowed Writes

- test stand scratch files only
- `.pf/artifacts/worker-lapaygroup-stand-dependency-probe-20260811.md`

## Forbidden Writes

- product source files
- Joomla extension manifests
- installer scripts
- release package config

## Checks

- PHP version is `>= 8.3`.
- Joomla vendor autoload exists.
- PSR HTTP interfaces exist.
- `Joomla\Http\Http` is usable as `Psr\Http\Client\ClientInterface`.
- Laminas Diactoros request, stream and uploaded-file factories exist.
- `lapaygroup/russianpost:2.0.0` can be installed or downloaded into an isolated
  scratch directory.

## Output

Write `.pf/artifacts/worker-lapaygroup-stand-dependency-probe-20260811.md`
with commands, PASS/FAIL status, dependency versions and blockers. Do not print
secrets.
