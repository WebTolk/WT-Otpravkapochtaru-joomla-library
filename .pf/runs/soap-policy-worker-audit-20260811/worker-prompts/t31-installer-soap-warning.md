# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t31-installer-soap-warning`
- run_id: `soap-policy-worker-audit-20260811`
- mode: read-only product audit
- workspace_access_file: `.pf/runtime/agent-runs/soap-policy-worker-audit-20260811/t31-installer-soap-warning/workspace-access.json`

## One Job

Verify the Joomla installer-side SOAP behavior in code and language strings.
Keep this narrow. Do not use browser tools, MCP tools, or local documentation.
Use simple shell reads/searches only.

## Read Scope

- `script.php`
- `language/en-GB/pkg_lib_wt_otpravkapochtaru.sys.ini`
- `language/ru-RU/pkg_lib_wt_otpravkapochtaru.sys.ini`

## Checks

- Installer preflight required extension list does not include `soap`.
- Installer preflight still checks `mbstring`.
- `renderInstallationMessage()` or equivalent post-install/post-update message path warns when `extension_loaded('soap')` is false.
- Warning is not emitted for uninstall.
- Both English and Russian language files contain the warning key.
- No product code edits are made.

## Output

Write only `.pf/artifacts/worker-soap-policy-installer-warning-audit-20260811.md`.
Use ASCII-only English text. Include verdict: `pass`, `needs-fix`, or `blocked`,
evidence bullets with file references, and residual risk.
