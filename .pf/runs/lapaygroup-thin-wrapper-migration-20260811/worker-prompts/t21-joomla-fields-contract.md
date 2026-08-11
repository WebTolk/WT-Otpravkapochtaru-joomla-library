# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t21-joomla-fields-contract`
- run_id: `lapaygroup-thin-wrapper-migration-20260811`
- mode: read-only planning

## One Job

Define the generic Joomla Form fields contract for Russian Post Otpravka entity
work.

## Read Scope

- `lib_webtolk_otpravkapochtaru/src/Fields/**`
- `lib_webtolk_otpravkapochtaru/src/Service/LinkedSelectOptionsService.php`
- field language strings if needed
- media/webasset files if needed

## Rules

- Do not change files.
- Do not inspect or mention JoomShopping as a library dependency.
- Treat fields as consumer-neutral Joomla Form fields.
- Keep field error behavior explicit: API errors must not fatal form rendering.

## Output

Write `.pf/artifacts/worker-thin-wrapper-joomla-fields-contract-20260811.md`
with:

- field list
- data source needed from LapayGroup SDK or wrapper service
- form attributes/field dependencies
- webasset requirements
- error/fallback behavior
- what must remain generic and consumer-neutral
