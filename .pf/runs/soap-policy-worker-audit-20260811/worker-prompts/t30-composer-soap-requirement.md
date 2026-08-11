# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t30-composer-soap-requirement`
- run_id: `soap-policy-worker-audit-20260811`
- mode: read-only product audit
- workspace_access_file: `.pf/runtime/agent-runs/soap-policy-worker-audit-20260811/t30-composer-soap-requirement/workspace-access.json`

## One Job

Verify the Composer/GitHub build-side dependency policy after the SOAP correction.

## Read Scope

- `composer.json`
- `README.md`
- `.github/` if present
- `.pf/artifacts/joomla-system-requirements-php83-mbstring-20260811.md`
- `.pf/artifacts/orchestrator-release-readiness-worker-review-20260811.md`

## Checks

- `composer.json` requires PHP `>=8.3.0`.
- `composer.json` requires `ext-mbstring`, `ext-simplexml`, and `ext-soap`.
- `composer.json` platform PHP is `8.3.0`.
- README distinguishes ready Joomla ZIP behavior from Composer/GitHub build behavior.
- No evidence says Joomla install must block on missing SOAP.

## Output

Write only `.pf/artifacts/worker-soap-policy-composer-audit-20260811.md`.
Use ASCII-only English text. Include verdict: `pass`, `needs-fix`, or `blocked`,
evidence bullets with file references, and residual risk.
