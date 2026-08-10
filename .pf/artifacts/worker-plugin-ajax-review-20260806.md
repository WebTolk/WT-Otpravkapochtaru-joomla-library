# Review: t03-plugin-ajax-endpoints

- timestamp: 2026-08-06T10:02:54+04:00
- reviewer: Codex orchestrator
- worker: shell-worker-plugin-ajax
- model: gpt-5.3-codex-spark
- driver: .pf/runtime-drivers/codex-exec-workspace-write.yaml
- status: accepted-after-orchestrator-fixes

## Scope Reviewed

- `plg_system_wt_otpravkapochtaru/src/Extension/WtOtpravkapochtaru.php`
- `plg_system_wt_otpravkapochtaru/src/Service/AjaxShippingOptionsService.php`
- `plg_system_wt_otpravkapochtaru/language/en-GB/plg_system_wt_otpravkapochtaru.ini`
- `plg_system_wt_otpravkapochtaru/language/ru-RU/plg_system_wt_otpravkapochtaru.ini`
- `tests/Unit/PluginAjax/AjaxShippingOptionsServiceTest.php`
- `tests/bootstrap.php`

## Worker Result

The worker completed under the write-capable PowerShell fallback driver and produced product-code changes for secure `com_ajax` endpoints plus a helper service and unit tests.

## Orchestrator Fixes

- Removed `ArrayHelper::contains()` usage because Joomla `ArrayHelper` does not provide that method in the checked local Joomla core versions.
- Replaced it with strict `in_array($mailType, $mailTypeList, true)`.
- Made the AJAX event handlers callable by Joomla 4 `triggerEvent` as well as Joomla 5/6 `AjaxEvent` dispatch by accepting a nullable event and returning the result.
- Replaced English text added to the Russian language file with Russian translations.
- Updated `tests/bootstrap.php` to the current local Joomla core path: `D:/.agents/docs/joomla/core/Joomla-core/6.x/6.1.0`.

## Security Review

- The endpoint allows only `GET` and `POST`.
- Joomla CSRF token validation is required through `Session::checkToken('get') || Session::checkToken('post')`.
- AJAX actions are allowlisted: `getMailTypes`, `getMailCategories`.
- `postoffice_code` is required and must match exactly six digits.
- `mail_type` is required for category lookup and must be present in the selected OPS mail-type list before categories are returned.
- Error responses use translated generic messages and do not expose credentials, raw API exceptions, or transport details.

## Verification

- `php -l` passed for:
  - `WtOtpravkapochtaru.php`
  - `AjaxShippingOptionsService.php`
  - `AjaxShippingOptionsServiceTest.php`
  - `tests/bootstrap.php`
- Targeted PHPUnit passed:
  - command: `php D:/.agents/tools/php-qa/vendor/bin/phpunit --configuration=<abs phpunit.xml> <abs AjaxShippingOptionsServiceTest.php>`
  - result: `OK (4 tests, 4 assertions)`

## Residual Risk

- Joomla.local browser/runtime assurance is still pending under `t04`.
- Field assets and XML integration are still pending under `t02`, so the AJAX endpoints are not yet exercised by the linked-list UI.
