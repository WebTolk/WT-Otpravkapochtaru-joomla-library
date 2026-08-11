# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t10-lapaygroup-data-parity-risk-matrix`
- run_id: `lapaygroup-russianpost-joomla-local-validation-20260811`
- mode: read-only product code

## Goal

Compare current Joomla library behavior with `lapaygroup/russianpost` 2.0.0 and
identify data-loss or API-contract risks before any product migration.

## Compare Surfaces

- settings/account info
- user shipping points
- mail type/category source data used by linked fields
- tariff calculation
- order create/edit/delete payloads and responses
- batch and document APIs
- postoffice APIs
- tracking APIs and credentials model

## Output

Write `.pf/artifacts/worker-lapaygroup-data-parity-risk-matrix-20260811.md`
with a table:

- current method
- upstream method/class
- input compatibility
- output compatibility
- mutation risk
- data-loss risk
- migration recommendation

Do not change product code.
