# Worker Launch Prompt

You are a Process Forge shell-worker reviewer.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t33-soap-policy-review`
- run_id: `soap-policy-worker-audit-20260811`
- mode: review
- workspace_access_file: `.pf/runtime/agent-runs/soap-policy-worker-audit-20260811/t33-soap-policy-review/workspace-access.json`

## One Job

Review T30-T32 reports and decide whether the corrected SOAP policy is ready.

## Read Scope

- `.pf/artifacts/worker-soap-policy-composer-audit-20260811.md`
- `.pf/artifacts/worker-soap-policy-installer-warning-audit-20260811.md`
- `.pf/artifacts/worker-soap-policy-package-local-smoke-20260811.md`
- `composer.json`
- `script.php`
- language files
- `.pf/artifacts/joomla-system-requirements-php83-mbstring-20260811.md`

## Checks

- Reconcile contradictory worker findings if any.
- Confirm Composer/GitHub and Joomla installer policies are separated.
- Confirm there is no product-code change request left inside worker outputs.
- Call out residual risks only when backed by evidence.

## Output

Write only `.pf/artifacts/reviewer-soap-policy-worker-audit-20260811.md`.
Use ASCII-only English text. Include verdict: `pass`, `needs-fix`, or `blocked`,
accepted findings, rejected findings, and residual risks.
