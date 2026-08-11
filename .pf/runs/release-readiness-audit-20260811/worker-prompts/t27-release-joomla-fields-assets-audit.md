# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t27-release-joomla-fields-assets-audit`
- run_id: `release-readiness-audit-20260811`
- mode: read-only product audit
- workspace_access_file: `.pf/runtime/agent-runs/release-readiness-audit-20260811/t27-release-joomla-fields-assets-audit/workspace-access.json`

## One Job

Audit the generic Joomla Form fields and webasset boundary.

## Read Scope

- `lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/OpslistField.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/MailtypesField.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/MailcategoriesField.php`
- `lib_webtolk_otpravkapochtaru/media/joomla.asset.json`
- `lib_webtolk_otpravkapochtaru/otpravkapochtaru.xml`
- `plg_system_wt_otpravkapochtaru/wtotpravkapochtaru.xml`
- local Joomla docs/core for form field layout and WebAssetManager when useful

## Checks

- Linked select field uses Joomla WebAssetManager from the field path.
- The asset name is library-owned, not plugin-owned.
- The field still uses native Joomla list field rendering; no copied system layout is required.
- Specialized fields remain generic library fields and do not mention JoomShopping.
- Asset installation path matches Joomla media destination rules.

## Output

Write only `.pf/artifacts/worker-release-joomla-fields-assets-audit-20260811.md`.
Use ASCII-only English text. Include verdict: `pass`, `needs-fix`, or `blocked`,
evidence bullets with file references, and any residual risk.
