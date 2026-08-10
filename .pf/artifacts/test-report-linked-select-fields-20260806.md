# Test Report: linked OPS/type/category fields

Date: 2026-08-06
Run: `linked-otpravka-select-fields-20260806`
Scope: `t04-assurance-joomla-local`
Reviewer: Codex orchestrator

## Summary

Status: passed with one runtime limitation.

The implementation was verified after worker delivery. The `t04` shell-worker produced a partial/mojibake report, so its output was treated as raw evidence only. The orchestrator repeated the decisive checks on the current workspace and on `joomla.local`.

Verified chain:

1. `user_shipping_points` (OPS)
2. `user_available_mail_types` (mail type, watches OPS)
3. `user_available_mail_category` (mail category, watches OPS and parent mail type)

## Worker And Runtime Notes

- Worker runtime: `codex-exec-workspace-write`
- Worker model: `gpt-5.3-codex-spark`
- Worker reasoning effort: `medium`
- Worker fallback: PowerShell, approved by user after PHPStorm MCP callable-tool gap was documented for evolve.
- PHPStorm MCP issue artifact: `.pf/artifacts/evolve/knowledge-candidates/kc-codex-exec-phpstorm-mcp-runtime-gap-20260806.yaml`
- Queued evolve inbox copy: `D:\.agents\processforge-workplace\learning\inbox\kc-codex-exec-phpstorm-mcp-runtime-gap-20260806.yaml`

## CLI QA

Passed:

- `php -l` on changed PHP classes.
- `node --check plg_system_wt_otpravkapochtaru/media/js/linked-select-fields.js`.
- PHPUnit via shared PHP QA tools: `OK (11 tests, 12 assertions)`.
- PHPCS on changed files: no errors.
- PHPStan on changed PHP files: `[OK] No errors`.
- PHP CS Fixer dry-run on changed files: no files to fix.

Note: the project has no local `vendor/bin`; shared PHP QA tools from `D:\.agents\tools\php-qa` were used.

## Package

Command:

`phing -f D:\Dev\WT-Otpravkapochtaru-joomla-library\phing.xml "3. Package release"`

Result:

- Package: `.packages/WT Otpravkapochtaru_3.0.0.zip`
- Size: 107257 bytes
- ZIP entry count: 43
- Required runtime entries present, including linked field classes, services, `joomla.asset.json`, and `linked-select-fields.js`.
- Development entries absent: `.codex/`, `tests/`, `tools/`, `composer.json`, `phpunit.xml`, `phpstan.neon`, `phpcs.xml`, `phing.xml`, `.php-cs-fixer.dist.php`.

## Joomla.local Installation Evidence

Test stand: `http://joomla.local/administrator/`

The package was installed through Joomla administrator installer before the final package rebuild. After rebuild, source and installed key file hashes were compared and matched.

Installed files verified:

- `libraries/webtolk/otpravkapochtaru/src/Fields/LinkedSelectField.php`
- `libraries/webtolk/otpravkapochtaru/src/Fields/MailtypesField.php`
- `libraries/webtolk/otpravkapochtaru/src/Fields/MailcategoriesField.php`
- `libraries/webtolk/otpravkapochtaru/src/Service/LinkedSelectOptionsService.php`
- `plugins/system/wt_otpravkapochtaru/src/Service/AjaxShippingOptionsService.php`
- `media/plg_system_wt_otpravkapochtaru/joomla.asset.json`
- `media/plg_system_wt_otpravkapochtaru/js/linked-select-fields.js`

All listed source-to-installed SHA-256 comparisons matched.

## Browser JS Evidence

Browser check used Playwright with installed Microsoft Edge because the bundled Playwright Chromium executable was not present in `C:\Users\musst\AppData\Local\ms-playwright`.

The test loaded the installed asset:

`/media/plg_system_wt_otpravkapochtaru/js/linked-select-fields.js`

Synthetic Joomla form controls used real Joomla-style field names:

- `jform[params][user_shipping_points]`
- `jform[params][user_available_mail_types]`
- `jform[params][user_available_mail_category]`

Mocked `com_ajax` response format:

`{ success: true, data: [{ options: [...] }] }`

Observed browser result:

- Request 1: `action=getMailTypes`, `postoffice_code=123456`, token present.
- Request 2: `action=getMailCategories`, `postoffice_code=123456`, `mail_type=PARCEL`, token present.
- Request 3: `action=getMailCategories`, `postoffice_code=123456`, `mail_type=EMS`, token present.
- Initial type options became `PARCEL`, `EMS`.
- After selecting `EMS`, category options became `WITH_DECLARED_VALUE`.
- Selected category became `WITH_DECLARED_VALUE`.

This proves the client script handles Joomla's array-wrapped `com_ajax` payload and the intended `OPS -> type -> category` cascade.

## AJAX Security Evidence

Browser check used a real Joomla administrator session and `Joomla.getOptions('csrf.token')`.

Observed `com_ajax` results:

- Invalid action: HTTP `400`, `success=false`, message `Unsupported AJAX action`.
- Invalid `postoffice_code=abc`: HTTP `400`, `success=false`, message `Post office code must be exactly 6 digits`.
- Missing token: HTTP `403`, `success=false`, Joomla invalid security token message.
- Valid `getMailTypes` request with `postoffice_code=123456` and token: HTTP `502`, `success=false`, message `Unable to load shipment options from API`.

The valid request fails safely on the stand because real Otpravka API credentials/data are not available there. No stack trace, local path, credential, or raw upstream response was exposed.

## Residual Risk

No real consuming extension form with these three fields was found/used on `joomla.local` during this pass. Therefore, saved-value behavior inside an actual business form remains unproven on the stand.

Coverage for that gap:

- Unit tests cover API-data extraction and fallback structures.
- Browser synthetic DOM test covers the installed JS cascade.
- Server runtime test covers `com_ajax` security behavior.

Full end-to-end form confirmation still requires a concrete Joomla form consuming `OpslistField`, `MailtypesField`, and `MailcategoriesField`.

## 2026-08-10 Native Layout/WebAsset Retest

Status: passed.

- Corrected Joomla asset URI from `plg_system_wtotpravkapochtaru/js/linked-select-fields.js` to `plg_system_wtotpravkapochtaru/linked-select-fields.js`; Joomla resolves script assets through the media `js` folder.
- Added package installer lifecycle for the custom field layout under `layouts/libraries/webtolk/otpravkapochtaru/form/field/linkedselect.php`.
- Removed duplicate legacy system plugin extension id `317`; after reinstall only `wtotpravkapochtaru` and the JoomShopping addon plugin remain enabled.
- Rebuilt package: `.packages/WT Otpravkapochtaru_3.0.0.zip`, `48` entries, `117469` bytes.
- Installed package on `joomla.local` with Joomla CLI: OK.
- Chrome DevTools runtime evidence:
  - system plugin form loaded `/media/plg_system_wtotpravkapochtaru/js/linked-select-fields.js`, guard `true`, mail type `7` options, mail category `4` options;
  - JoomShopping shipping price form loaded the same asset, guard `true`, `sm_params_user_available_mail_types` had `7` options, `sm_params_user_available_mail_category` had `4` options;
  - `getMailTypes` and `getMailCategories` AJAX calls returned HTTP `200`.
- Regression checks:
  - PHP syntax checks passed for `script.php`, `LinkedSelectField.php`, and the layout;
  - `node --check` passed for `linked-select-fields.js`;
  - PHPUnit Unit suite passed: `OK (11 tests, 12 assertions)`.

## 2026-08-10 GetInput Without Layout Retest

Status: passed.

- Removed both test-stand `linkedselect.php` files:
  - root layout override under `layouts/libraries/...`;
  - library source layout under `libraries/Webtolk/Otpravkapochtaru/layouts/...`.
- Changed `LinkedSelectField` to use Joomla's native `joomla.form.field.list` layout and activate the linked-select script in `getInput()`.
- Rebuilt package: `.packages/WT Otpravkapochtaru_3.0.0.zip`, `47` entries, `115677` bytes.
- ZIP inspection: no `linkedselect.php` and no linked layout entries.
- Installed rebuilt ZIP on `joomla.local`: OK.
- Post-install PhpStorm MCP search: no `linkedselect.php` under `layouts/**` or `libraries/**`.
- Chrome DevTools on JoomShopping shipping price form:
  - `/media/plg_system_wtotpravkapochtaru/js/linked-select-fields.js` loaded with HTTP `200`;
  - JS guard `window.WtOtpravkapochtaruLinkedSelectFieldsLoaded === true`;
  - `sm_params_user_available_mail_types`: value `EMS`, `7` options;
  - `sm_params_user_available_mail_category`: value `ORDINARY`, `4` options;
  - `getMailTypes` and `getMailCategories`: HTTP `200`.
- Regression checks:
  - PHP syntax checks passed for `LinkedSelectField.php` and `script.php`;
  - `node --check` passed for `linked-select-fields.js`;
  - PHPUnit Unit suite passed: `OK (11 tests, 12 assertions)`.

## 2026-08-10 Final No-Legacy Retest

Status: passed. This section supersedes the previous retest counts and any references to the removed legacy AJAX service.

- Source scan:
  - no `watchfield`;
  - no `parentfield`;
  - no `getFormValue`;
  - no `getWatchfieldName`;
  - no `getParentfieldName`;
  - no `getAjaxUrl`;
  - no `AjaxShippingOptionsService`;
  - no `onAjaxWt_otpravkapochtaru`;
  - no `ALLOWED_ACTIONS`;
  - no `POSTOFFICE_CODE_LENGTH`;
  - no `hasValidAjaxToken`;
  - no `getAjaxService`;
  - no `ajaxService`.
- Product checks:
  - `php -l` passed for `LinkedSelectField.php`, `MailtypesField.php`, `MailcategoriesField.php`, `LinkedSelectOptionsService.php`, and `WtOtpravkapochtaru.php`;
  - `node --check plg_system_wt_otpravkapochtaru/media/js/linked-select-fields.js`: passed;
  - `powershell -NoProfile -ExecutionPolicy Bypass -File tools/qa/lint-php.ps1`: passed;
  - `D:\OSPanel\modules\PHP-8.3\php.exe D:\.agents\tools\php-qa\vendor\bin\phpunit --configuration=phpunit.xml --colors=never`: `OK (7 tests, 8 assertions)`;
  - targeted product `git diff --check`: passed.
- Package checks:
  - command: `phing -f "D:\Dev\WT-Otpravkapochtaru-joomla-library\phing.xml" "3. Package release"`;
  - archive: `.packages/WT Otpravkapochtaru_3.0.0.zip`;
  - size: `108292` bytes;
  - entries: `42`;
  - obsolete entries: `0`.
- Joomla local install:
  - command: `D:\OSPanel\modules\PHP-8.3\php.exe D:\OSPanel\home\joomla.local\public\cli\joomla.php extension:install --path="D:\Dev\WT-Otpravkapochtaru-joomla-library\.packages\WT Otpravkapochtaru_3.0.0.zip" --no-interaction`;
  - result: `Extension installed successfully`.
- JoomShopping runtime:
  - form: `administrator/index.php?option=com_jshopping&controller=shippingsprices&task=edit&sh_pr_method_id=1&shipping_id_back=`;
  - linked script: HTTP `200`;
  - JS guard: `true`;
  - mail type requestfields: `{"postoffice_code":"user_shipping_points"}`;
  - mail category requestfields: `{"postoffice_code":"user_shipping_points","mail_type":"user_available_mail_types"}`;
  - mail type options for OPS `109012`: `7`;
  - mail category options for `EMS`: `4`;
  - AJAX `getMailTypes`: HTTP `200`;
  - AJAX `getMailCategories`: HTTP `200`.
- Console:
  - no linked-select JavaScript errors observed;
  - only local HTTP COOP warning and unrelated form accessibility issues were reported.

## 2026-08-10 Strict Options Follow-Up

Status: passed.

- Removed `ensureValueOption()` from `LinkedSelectField`.
- Removed saved-value option injection from `MailtypesField` and `MailcategoriesField`.
- Verification:
  - `php -l lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php`: passed;
  - `php -l lib_webtolk_otpravkapochtaru/src/Fields/MailtypesField.php`: passed;
  - `php -l lib_webtolk_otpravkapochtaru/src/Fields/MailcategoriesField.php`: passed;
  - PHPCS for the three field files: passed;
  - PHPUnit Unit suite: `OK (7 tests, 8 assertions)`;
  - targeted `git diff --check`: passed;
  - `phing -f "D:\Dev\WT-Otpravkapochtaru-joomla-library\phing.xml" "3. Package release"`: passed;
  - archive `.packages/WT Otpravkapochtaru_3.0.0.zip`: `42` entries, `108036` bytes;
  - packaged `LinkedSelectField.php` contains no `ensureValueOption`.

## 2026-08-10 Release Readiness Pass

Status: passed.

- Static/code checks:
  - legacy linked-select scan: no matches for `watchfield`, `parentfield`, `getFormValue`, `getWatchfieldName`, `getParentfieldName`, `getAjaxUrl`, `ensureValueOption`, `AjaxShippingOptionsService`, `onAjaxWt_otpravkapochtaru`, `ALLOWED_ACTIONS`, `POSTOFFICE_CODE_LENGTH`, `hasValidAjaxToken`, `getAjaxService`, `ajaxService`, or `fallback`;
  - PHP syntax lint for `script.php`, library, plugin, and tests: passed;
  - PHPUnit: `OK (10 tests, 25 assertions)`;
  - `node --check plg_system_wt_otpravkapochtaru/media/js/linked-select-fields.js`: passed;
  - PHPCS with `phpcs.xml`: passed;
  - PHPStan with `phpstan.neon`: passed;
  - PHP CS Fixer dry-run: passed after mechanical style fixes.
- Package:
  - command: `phing -f "D:\Dev\WT-Otpravkapochtaru-joomla-library\phing.xml" "3. Package release"`;
  - archive: `.packages/WT Otpravkapochtaru_3.0.0.zip`;
  - size: `61599` bytes;
  - entries: `41`;
  - SHA-256: `670A7762C8789E22DE8D59BB35F66A131E9AD8510D7AF8D3DBE9CD76CA6313FA`;
  - package version: `3.0.0`;
  - plugin id: `wtotpravkapochtaru`;
  - plugin version: `3.0.0`;
  - library version: `3.0.0`;
  - forbidden entries: `0`.
- Joomla local:
  - installed final ZIP with Joomla CLI: `Extension installed successfully`;
  - JoomShopping shipping price form loaded `/media/plg_system_wtotpravkapochtaru/js/linked-select-fields.js` with HTTP `200`;
  - JS guard: `true`;
  - requestfields present on mail type and mail category fields;
  - OPS `109012`: `7` mail type options;
  - mail type `EMS`: `4` category options;
  - `getMailTypes` and `getMailCategories` AJAX requests returned HTTP `200`;
  - no linked-select JavaScript console errors observed.
- Explicit compatibility boundary:
  - compatibility for existing system plugin parameter names is preserved: `AccessToken`, `user_key_or_login_and_password`, and `user_auth_key`;
  - unit tests cover both existing system plugin parameter names and canonical direct `CredentialsProvider` parameter names;
  - no compatibility fallback remains in linked-select field attributes or AJAX event names.
