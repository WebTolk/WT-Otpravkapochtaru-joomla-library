# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t17-lapaygroup-core-swap-review`
- run_id: `lapaygroup-joomla-core-swap-20260811`
- mode: evidence review

## Goal

Review the completed T13-T16 artifacts and classify whether the direct
Joomla-core/vendor LapayGroup 2.0.0 substitution is proven enough for a product
migration proposal.

## Required Review Inputs

- `.pf/artifacts/worker-lapaygroup-core-swap-stand-snapshot-20260811.md`
- `.pf/artifacts/worker-lapaygroup-core-swap-writer-20260811.md`
- `.pf/artifacts/worker-lapaygroup-core-swap-sdk-smoke-20260811.md`
- `.pf/artifacts/worker-lapaygroup-core-swap-joomshopping-surface-20260811.md`
- relevant earlier LapayGroup artifacts in `.pf/artifacts`

## Required Findings

- Say whether product migration is accepted, rejected or still needs proof.
- Separate:
  - classloader/constructor proof
  - credentials proof
  - live API proof
  - JoomShopping form proof
  - package/runtime parity risk
- Check that no product repo files were modified.
- Check that no raw secrets were written to artifacts.
- Check that T14 produced restore instructions.

## Allowed Writes

- `.pf/artifacts/reviewer-lapaygroup-core-swap-20260811.md`
- `.pf/runtime/telemetry/**`

## Forbidden Writes

- product source files
- Joomla stand library/vendor/plugin files
- plugin params

## Output

Write `.pf/artifacts/reviewer-lapaygroup-core-swap-20260811.md`.
