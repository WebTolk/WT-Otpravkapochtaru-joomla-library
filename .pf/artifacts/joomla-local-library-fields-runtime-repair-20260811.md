# Joomla Local Library Fields Runtime Repair - 2026-08-11

## Scope

- Incident: linked library fields stopped updating in Joomla plugin settings.
- URL: `http://joomla.local/administrator/index.php?option=com_plugins&view=plugin&layout=edit&extension_id=389`
- Agent/role: Codex / Joomla library field runtime verification
- Product source changed: yes
- Local Joomla stand changed: yes, rebuilt package installed after verification

## Fields Checked

- `Webtolk\Otpravkapochtaru\Fields\AccountinfoField`
- `Webtolk\Otpravkapochtaru\Fields\OpslistField`
- `Webtolk\Otpravkapochtaru\Fields\MailtypesField`
- `Webtolk\Otpravkapochtaru\Fields\MailcategoriesField`
- `Webtolk\Otpravkapochtaru\Fields\LinkedSelectField`
- `media/lib_wt_otpravkapochtaru/js/linked-select-fields.js`

## Reproduction

After the response-stream fix, backend API calls succeeded and `account_info` rendered `API connected`, but linked selects still did not update.

Browser DOM before this fix:

- `jform_params_linked_test_shipping_point`: selected `109012`
- `jform_params_linked_test_mail_type`: only `Select an option`
- `jform_params_linked_test_mail_category`: only `Select an option`
- `window.WtOtpravkapochtaruLinkedSelectFieldsLoaded`: `false`
- no `linked-select-fields.js` script tag
- no `com_ajax` requests

## Root Causes

1. The linked-select JS asset was not present in the final plugin edit page HTML.
   - The fields had the expected `data-wt-url` and `data-wt-requestfields` metadata.
   - WebAssetManager registration from field rendering was not enough for this plugin edit lifecycle.
2. The JavaScript dependency resolver did not check Joomla plugin parameter field ids/names:
   - actual id: `jform_params_linked_test_shipping_point`
   - actual name: `jform[params][linked_test_shipping_point]`
   - previous resolver checked only `jform_<name>`, exact raw `name`, and suffix fallback.

## Fix

- Updated `LinkedSelectField`:
  - uses the library asset name `lib_wt_otpravkapochtaru.linked-select-fields`
  - registers the fallback script with media-relative URI `lib_wt_otpravkapochtaru/js/linked-select-fields.js`
  - appends one guarded direct script tag from the rendered field markup when WebAssetManager misses the asset in plugin/edit forms
- Updated `linked-select-fields.js`:
  - resolves `jform_params_<field>`
  - resolves `jform[params][<field>]`

## Verification

Static and unit checks:

- OSPanel PHP lint passed for `LinkedSelectField.php`
- `node --check` passed for `linked-select-fields.js`
- PHPUnit passed: `15 tests, 57 assertions`
- focused PHPCS passed for `LinkedSelectField.php`
- focused PHP-CS-Fixer dry run passed for `LinkedSelectField.php`
- PHPStan passed: no errors
- `git diff --check` passed with only existing CRLF warnings for unrelated `.pf` files

Package:

- Built `.packages/WT Otpravkapochtaru_3.0.0.zip`
- Archive entries: `65`
- Archive bytes: `211255`
- Archive SHA-256: `FE527800D39AACBBFF476F1B7D62D5F13D650A5D81DFC424FBCAC2BB96654B09`
- Archive contains all checked field classes, `RewindingPsr18Client.php`, `linked-select-fields.js`, and `joomla.asset.json`
- Joomla CLI install passed: `Extension installed successfully.`

Installed stand:

- `LinkedSelectField.php` hash matches repository: `46776522A676...`
- `linked-select-fields.js` hash matches repository: `2209DBFB4C06...`
- `RewindingPsr18Client.php` hash matches repository: `10EA15A48821...`

Browser runtime after package install:

- linked-select script loaded from `http://joomla.local/media/lib_wt_otpravkapochtaru/js/linked-select-fields.js`
- `window.WtOtpravkapochtaruLinkedSelectFieldsLoaded`: `true`
- `AccountinfoField`: shows `API connected`
- `OpslistField`: selected `109012 - ул. Никольская, д.7-9, стр.4, г. Москва`
- `MailtypesField`: loaded `7` options, selected `EMS`
- `MailcategoriesField`: loaded `4` options for `EMS`, selected `ORDINARY`
- AJAX requests:
  - `getMailTypes&postoffice_code=109012`: HTTP `200`
  - `getMailCategories&postoffice_code=109012&mail_type=EMS`: HTTP `200`
- Cascade interaction:
  - selected `SMALL_PACKET`
  - `getMailCategories&postoffice_code=109012&mail_type=SMALL_PACKET`: HTTP `200`
  - category list updated to `ORDERED / Registered`
- no visible `API request error`
- no visible `Shipping points list unavailable`
- browser console only contains the unrelated HTTP Cross-Origin-Opener-Policy warning

## Worker State

- No active `process-forge` / `shell-worker` / `.pf` worker process remained after verification.

## Verdict

All library fields exposed on the system plugin settings form are now operational on `joomla.local`. The linked fields failed because their controller script was missing from the page and the resolver did not recognize Joomla plugin params field ids/names.
