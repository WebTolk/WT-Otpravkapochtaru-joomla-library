# Reviewer Launch Prompt

You are a Process Forge reviewer shell-worker.
Requested model: `gpt-5.5`.
Requested reasoning effort: `high`.
You are not the orchestrator.

## Assignment

- task_id: `t11-lapaygroup-test-plan-review`
- run_id: `lapaygroup-russianpost-joomla-local-validation-20260811`
- mode: review only

## Goal

Review the LapayGroup RussianPost 2.0.0 Joomla local validation plan and worker
outputs before any product-code migration is proposed.

## Inputs

- `.pf/artifacts/lapaygroup-russianpost-joomla-local-test-plan-20260811.md`
- `.pf/runs/lapaygroup-russianpost-joomla-local-validation-20260811/plan.md`
- `.pf/runs/lapaygroup-russianpost-joomla-local-validation-20260811/task-index.md`
- expected worker reports:
  - `.pf/artifacts/worker-lapaygroup-stand-dependency-probe-20260811.md`
  - `.pf/artifacts/worker-lapaygroup-joomla-psr-transport-prototype-20260811.md`
  - `.pf/artifacts/worker-lapaygroup-runtime-smoke-20260811.md`
  - `.pf/artifacts/worker-lapaygroup-data-parity-risk-matrix-20260811.md`

## Review Questions

1. Does the plan keep product code read-only?
2. Does it prove the Joomla-way transport path:
   `Joomla\Http\Http` as PSR-18 client plus Laminas Diactoros PSR-17 factories?
3. Does it avoid Symfony HTTP Client as the target implementation path?
4. Are live API tests read-only and safe for a real Russian Post account?
5. Are credential and raw authorization values protected from artifacts?
6. Does the parity matrix cover the Joomla package's public API surface?
7. Is the PHP 8.3 requirement of `lapaygroup/russianpost` 2.0.0 handled as an
   explicit release gate?
8. Are there missing blockers that should stop any migration proposal?

## Output

Write `.pf/artifacts/reviewer-lapaygroup-test-plan-review-20260811.md`.

Use this structure:

- `status`: `approved`, `approved-with-conditions`, or `rejected`
- `findings`: ordered by severity
- `missing-evidence`
- `migration-risk-classification`: `safe`, `unsafe`, or `needs-more-proof`
- `required-follow-ups`

Do not change product code.
