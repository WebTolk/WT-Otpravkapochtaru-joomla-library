# Review: t02-library-fields-assets

- timestamp: 2026-08-06T10:23:00+04:00
- reviewer: Codex orchestrator
- worker: shell-worker-fields-assets
- model: gpt-5.3-codex-spark
- driver: .pf/runtime-drivers/codex-exec-workspace-write.yaml
- status: accepted-after-orchestrator-fixes

## Scope Reviewed

- `lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/MailtypesField.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/MailcategoriesField.php`
- `lib_webtolk_otpravkapochtaru/src/Service/LinkedSelectOptionsService.php`
- `plg_system_wt_otpravkapochtaru/media/js/linked-select-fields.js`
- `plg_system_wt_otpravkapochtaru/media/joomla.asset.json`
- `plg_system_wt_otpravkapochtaru/wt_otpravkapochtaru.xml`
- `tests/Unit/Fields/LinkedSelectOptionsServiceTest.php`
- `tests/bootstrap.php`
- `phpstan.neon`

## Worker Result

The worker created the linked field layer for the `OPS -> type -> category` chain:

- `MailtypesField` watches the OPS field through `watchfield`.
- `MailcategoriesField` watches OPS through `watchfield` and type through `parentfield`.
- The frontend controller reads `url`, sends Joomla token, refreshes options, and dispatches change events downstream.
- A library service extracts mail types from `user-available-mail-types` with fallback to `user-available-products[*].mail-type`, and categories from `user-available-products[*].mail-category` filtered by `mail-type`.

## Orchestrator Fixes

- Removed PHP 8.3-only typed class constants to preserve the project PHP 8.1 platform contract.
- Switched JS registration from legacy `addScript()` to Joomla WebAssetManager.
- Added `plg_system_wt_otpravkapochtaru/media/joomla.asset.json`.
- Corrected the asset URI to `plg_system_wt_otpravkapochtaru/js/linked-select-fields.js`.
- Removed duplicate `<folder>media</folder>` from plugin files and left Joomla `<media>` installation.
- Removed raw API exception messages from new admin select options.
- Removed `Text::_()` from the pure library option service because it requires a live Joomla application in unit tests.
- Added a unit test for the primary `user-available-mail-types` source.
- Updated local QA paths in `tests/bootstrap.php` and `phpstan.neon` to the current Joomla core docs root.
- Added a targeted PHPCS ignore for `onAjaxWt_otpravkapochtaru`; the underscore is required by Joomla `com_ajax` plugin event naming.

## Verification

- `php -l` passed for all changed PHP files in the feature slice.
- PHPUnit passed: `OK (11 tests, 12 assertions)`.
- `node --check plg_system_wt_otpravkapochtaru/media/js/linked-select-fields.js` passed.
- XML and JSON manifest validation passed.
- PHPCS passed for the changed PHP scope.
- PHPStan passed for the changed PHP scope with `--memory-limit=512M`.
- PHP CS Fixer dry-run passed for the changed PHP scope.

## Runtime Notes

- The worker used the user-approved PowerShell fallback because callable PHPStorm MCP tooling is still not available inside `codex-exec`.
- PF detached worker telemetry stayed stale after process exit; `status.json`/`exit.json` were corrected from observable evidence before `worker-run collect`.
- `Joomla.local` runtime assurance remains pending under `t04`.
