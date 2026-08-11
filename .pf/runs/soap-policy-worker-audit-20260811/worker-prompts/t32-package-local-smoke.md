# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t32-package-local-smoke`
- run_id: `soap-policy-worker-audit-20260811`
- mode: package/runtime audit
- workspace_access_file: `.pf/runtime/agent-runs/soap-policy-worker-audit-20260811/t32-package-local-smoke/workspace-access.json`

## One Job

Verify the built package and Joomla local smoke behavior for the corrected SOAP policy.

## Read/Runtime Scope

- `.packages/WT Otpravkapochtaru_3.0.0.zip`
- `script.php`
- language files
- `.pf/tmp/installer_soap_warning_probe.php`
- Joomla local stand at `D:/OSPanel/home/joomla.local/public`
- OSPanel PHP 8.3 at `D:/OSPanel/modules/PHP-8.3/php.exe`

## Allowed Runtime Action

You may run Joomla CLI `extension:install --path` against the existing `joomla.local`
stand with OSPanel PHP 8.3. Do not edit product source code.

## Checks

- ZIP exists and contains updated `script.php`.
- ZIP contains both localized warning language files.
- ZIP contains library-owned field web assets.
- Running the installer package on `joomla.local` with SOAP omitted from CLI extensions succeeds.
- The probe shows warning present without SOAP and absent with normal OSPanel PHP where SOAP is loaded.

## Output

Write only `.pf/artifacts/worker-soap-policy-package-local-smoke-20260811.md`.
Use ASCII-only English text. Include verdict: `pass`, `needs-fix`, or `blocked`,
commands run, evidence bullets, and residual risk.
