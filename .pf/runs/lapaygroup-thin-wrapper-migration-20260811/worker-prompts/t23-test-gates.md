# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t23-test-gates`
- run_id: `lapaygroup-thin-wrapper-migration-20260811`
- mode: read-only planning

## One Job

Define the smallest complete test and release gate matrix for the thin wrapper
migration.

## Read Scope

- current `tests/**`
- build/package scripts
- previous `.pf` update-test and LapayGroup artifacts
- Joomla local test evidence artifacts

## Rules

- Do not change files.
- No live mutation tests.
- Do not require JoomShopping-specific tests for the library.
- Include generic Joomla Form field tests.

## Output

Write `.pf/artifacts/worker-thin-wrapper-test-gates-20260811.md` with:

- unit tests
- Joomla local update tests
- classloader/package inspection tests
- generic field rendering tests
- read-only API smoke tests
- release blockers
- explicit acceptance criteria
