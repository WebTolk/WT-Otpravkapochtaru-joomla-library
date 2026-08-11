# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t19-lapaygroup-sdk-inventory`
- run_id: `lapaygroup-thin-wrapper-migration-20260811`
- mode: read-only planning

## One Job

Inventory the local `lapaygroup/russianpost` 2.0.0 SDK source and list exactly
which SDK classes can replace current forked transport/provider/entity/enum
code.

## Read Scope

- `.pf/tmp/LapayGroup-RussianPost-2.0.0/RussianPost-2.0.0/composer.json`
- `.pf/tmp/LapayGroup-RussianPost-2.0.0/RussianPost-2.0.0/src/**`

## Rules

- Do not change files.
- Do not use Composer or network.
- Do not inspect Joomla fields.
- Do not design package strategy; T22 owns that.

## Output

Write `.pf/artifacts/worker-thin-wrapper-lapaygroup-inventory-20260811.md`
with:

- SDK metadata summary
- providers table
- entities table
- enums/dictionaries table
- transport requirements
- methods that match current required Otpravka field/data needs
- obvious gaps
