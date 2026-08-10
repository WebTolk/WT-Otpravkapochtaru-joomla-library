# Worker Launch Control Review: linked select fields

Date: 2026-08-06
Run: `linked-otpravka-select-fields-20260806`

## Scope

Implementation workers were launched for:

- `t02-library-fields-assets`
- `t03-plugin-ajax-endpoints`

Required runtime policy:

- ProcessForge shell-workers must use model `gpt-5.3-codex-spark`.
- Reasoning level for this launch: `high`.
- Code and file work must go through MCP PHPStorm first.
- Shell-only fallback was not approved.
- Each worker report must contain PHPStorm MCP evidence.

## Result

Both workers were stopped by the orchestrator and their outputs were rejected.

- `t02-library-fields-assets`: `cancelled`, failure reason `stop requested`.
- `t03-plugin-ajax-endpoints`: `cancelled`, failure reason `stop requested`.
- No worker report was collected as accepted delivery.
- No product-code implementation is accepted from this attempt.

## Evidence

The worker logs show that workers had the PHPStorm requirement in their prompt and workspace access metadata, but performed code/file work through shell commands instead.

Observed examples:

- `t02` used PowerShell `Get-Content`, `New-Item`, and `Set-Content` for project files.
- `t02` created out-of-scope probe file `tests/temp_write_probe.txt`.
- `t02` attempted to create PHP files under:
  - `lib_webtolk_otpravkapochtaru/src/Service/LinkedSelectOptionsService.php`
  - `lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php`
  - `lib_webtolk_otpravkapochtaru/src/Fields/MailtypesField.php`
  - `lib_webtolk_otpravkapochtaru/src/Fields/MailcategoriesField.php`
- `t02` attempted XML edits in `plg_system_wt_otpravkapochtaru/wt_otpravkapochtaru.xml` through `Set-Content`.
- `t03` used shell `rg` and `Get-Content` for code and local Joomla documentation inspection.
- No accepted worker output proved actual callable MCP PHPStorm code/file operations.

The logs also show that workspace metadata listed `phpstorm` as available/activated, but that is not sufficient evidence of tool use. The explicit task rule required the worker to verify and use MCP PHPStorm before code edits, or stop.

## Cleanup

The orchestrator removed the unaccepted files created by `t02` and restored the touched XML formatting.

Removed unaccepted files:

- `lib_webtolk_otpravkapochtaru/src/Service/LinkedSelectOptionsService.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/MailtypesField.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/MailcategoriesField.php`
- `tests/temp_write_probe.txt`

Post-cleanup check:

- `git diff --stat -- lib_webtolk_otpravkapochtaru plg_system_wt_otpravkapochtaru tests composer.json` returned no product-code diff.
- `git status --short -- lib_webtolk_otpravkapochtaru plg_system_wt_otpravkapochtaru tests composer.json` returned no product-code changes.

## Driver Finding

The current `codex-exec` ProcessForge runtime driver is a shell driver.

`runtime-driver-describe --driver codex-exec` reports:

- `kind: shell`
- wrapper: `{processforge_root}/tools/codex_exec_worker.py`
- `PF_CODEX_SANDBOX: read-only`
- `security.allow_shell: false`

Inspection of `codex_exec_worker.py` showed that it starts `codex exec` with prompt, capsule, workspace paths and output paths. It does not prove or enforce that callable MCP PHPStorm tools are injected into the worker session.

This explains the failed attempt: the workers received PHPStorm as requirement/metadata, but did not use a callable PHPStorm MCP tool and fell back to shell.

## Blocking Condition

Do not relaunch `t02-library-fields-assets` or `t03-plugin-ajax-endpoints` through the same `codex-exec` path until one of these is true:

- a ProcessForge worker driver exists that exposes callable PHPStorm MCP tools to the worker session;
- or a manual/IDE-backed worker mode is selected and recorded;
- or the orchestrator explicitly approves a different fallback policy.

Until then, the implementation tasks remain open with failed iterations.

## Current Technical Plan Remains Valid

The target dependency chain remains:

`OPS -> type -> category`

- `user_shipping_points` selects the OPS.
- `user_available_mail_types` watches `user_shipping_points`.
- `user_available_mail_category` watches `user_available_mail_types` and also receives parent OPS from `parentfield="user_shipping_points"`.
- `watchfield` remains the selected attribute name.
- Request URLs are relative `com_ajax` URLs to the system plugin.
- AJAX endpoints must be security-reviewed before acceptance: CSRF token, strict input normalization, configured plugin credentials only, no raw HTML legacy response, Joomla JSON response APIs, no secret leakage.
