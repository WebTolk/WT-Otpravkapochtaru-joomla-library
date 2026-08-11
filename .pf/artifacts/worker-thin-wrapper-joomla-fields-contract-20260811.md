# Joomla Form fields contract: Russian Post Otpravka entity (read-only shell-worker contract)

- `run_id`: `lapaygroup-thin-wrapper-migration-20260811`
- `task_id`: `t21-joomla-fields-contract`
- `scope`: generic Joomla Form fields provided by `lib_webtolk_otpravkapochtaru`

## 1) Field list

- `accountinfo` (`AccountinfoField`, extends `Joomla\CMS\Form\Field\NoteField`)
  - Purpose: render read-only API/account health summary card inside a settings form.
  - Source class: `lib_webtolk_otpravkapochtaru/src/Fields/AccountinfoField.php`.

- `opslist` (`OpslistField`, extends `ListField`)
  - Purpose: list shipping points (`postoffice_code` values) as `<option>` entries.
  - Source class: `lib_webtolk_otpravkapochtaru/src/Fields/OpslistField.php`.

- `mailtypes` (`MailtypesField`, extends `LinkedSelectField`)
  - Purpose: list linked `mail_type` options for a selected post office.
  - Source class: `lib_webtolk_otpravkapochtaru/src/Fields/MailtypesField.php`.

- `mailcategories` (`MailcategoriesField`, extends `LinkedSelectField`)
  - Purpose: list linked `mail_category` options for selected post office + selected mail type.
  - Source class: `lib_webtolk_otpravkapochtaru/src/Fields/MailcategoriesField.php`.

- `LinkedSelectField` (`LinkedSelectField`, abstract)
  - Purpose: shared base for linked selects: adds request mapping + web asset, resolves dependency values.
  - Source class: `lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php`.

## 2) Data source and wrappers

- All dynamic shipping data is sourced from the library client wrapper:
  - `new Otpravkapochtaru(new CredentialsProvider())`.
  - Main transport call used by list fields: `getShippingPoints()`.
  - Account status field also uses:
    - `getAccountInfo()`
    - `getApiLimit()`
- Raw payload transformation is centralized in `LinkedSelectOptionsService` (`lib_webtolk_otpravkapochtaru/src/Service/LinkedSelectOptionsService.php`).
- Shipping-point shape consumed by service:
  - `operator-postcode` (required for option value)
  - `ops-address` / `address` / `operator-address` (label fallback)
  - `user-available-mail-types` or `user-available-products[].mail-type`
  - `user-available-products[].mail-category`
  - `mail-type`, `mail-category` are normalized to unique sorted scalars.
- Label resolution for options:
  - mail type value > `PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_MAIL_TYPE_*`
  - mail category value > `PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_MAIL_CATEGORY_*`
  - unresolved/missing constants fall back to raw value.

## 3) Form attributes and field dependencies

- `accountinfo`
  - No explicit dependency attributes.
  - Reads form params directly via `$this->form->getData()->get('params', [])`.
  - In plugin XML example, it is placed before auth/edit inputs and does not post back values.

- `opslist`
  - Generic standalone list field.
  - No `requestfields` / `url` dependencies.
  - In plugin XML usage, it is the dependency source (`name=linked_test_shipping_point`) for mail selects.

- `mailtypes`
  - Requires one dependency via `requestfields` map:
    - request key: `postoffice_code`
    - form field name: e.g. `linked_test_shipping_point`
  - Requires `url` attribute pointing to AJAX action.
  - In plugin XML:
    - `requestfields="{\"postoffice_code\":\"linked_test_shipping_point\"}"
    - `url="index.php?option=com_ajax&plugin=wtotpravkapochtaru&group=system&format=json&action=getMailTypes"`

- `mailcategories`
  - Requires two dependencies via `requestfields` map:
    - `postoffice_code` -> source field name (e.g. `linked_test_shipping_point`)
    - `mail_type` -> source field name (e.g. `linked_test_mail_type`)
  - Requires `url` attribute to endpoint.
  - In plugin XML:
    - `requestfields="{\"postoffice_code\":\"linked_test_shipping_point\",\"mail_type\":\"linked_test_mail_type\"}"
    - `url="index.php?option=com_ajax&plugin=wtotpravkapochtaru&group=system&format=json&action=getMailCategories"`

- Generic requestfield contract for consumer implementations
  - `requestfields` is a JSON object: `{ requestParam: formFieldName }`.
  - Field name matching by selector:
    - first by `#jform_<fieldName>`
    - then exact `name`
    - then suffix match `[name$='[<fieldName>]']`
  - JS only proceeds when all mapped request values are non-empty.

## 4) Webasset requirements

- Required asset name: `plg_system_wtotpravkapochtaru.linked-select-fields`.
- Source path: `plg_system_wt_otpravkapochtaru/linked-select-fields.js` (from plugin media folder).
- Registry metadata:
  - `plg_system_wt_otpravkapochtaru/media/joomla.asset.json` registers script with `dependencies: ["core"]` and `defer: true`.
- Field-side activation:
  - `LinkedSelectField::useLinkedSelectScript()` does:
    - if asset already exists, `useScript(name)`
    - else `registerAndUseScript(name, 'plg_system_wtotpravkapochtaru/linked-select-fields.js', [], {defer: true}, ['core'])`

- JS endpoint contract consumed by script:
  - `payload.data` object must include `options` array.
  - Each option item: `{ value: string, text?: string }`.
  - Fetch layer accepts failure payloads with `{ message?: string }` but falls back silently.

## 5) Error/fallback behavior

- API/plugin/config failures must not crash rendering.
  - `accountinfo` catches:
    - `ConfigurationException` -> warning text
    - `TransportException` -> danger text with unauthorized branch and error details
    - generic `Exception` -> danger text with exception message
    - empty API response -> warning+description
  - `opslist`, `mailtypes`, `mailcategories` catch:
    - disabled plugin, missing config, API/transport errors
    - internal exceptions
    - return single placeholder option describing failure.
  - `LinkedSelectOptionsService::resolveLabel` catches `Throwable` during language load/lookup and returns raw value.

- Empty/invalid option states:
  - missing dependency values:
    - `mailtypes`/`mailcategories` return `JGLOBAL_SELECT_AN_OPTION` when no current value exists
    - with existing current value, preserve it as single fallback option.
  - no options found from service -> `PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_OPSLIST_EMPTY` placeholder.
  - JS `linked-select-fields.js`:
    - no data dependency map -> clears to disabled empty option
    - fetch in progress disables select and restores previous enabled state after
    - non-2xx/invalid payload/fetch errors -> disabled empty option and dispatch change
    - does not render thrown errors in UI (silent fallback behavior).

## 6) Generic Joomla Form contract constraints

- Keep fields generic by treating all values/labels as opaque API values.
- Do not embed consumer-specific field semantics (no cart/order integration assumptions).
- Consumer can use any form context (`name`/fieldset can differ) as long as:
  - field types are registered under a form include path
  - `requestfields` JSON maps request params to existing form field names
  - linked-select endpoint returns `{ options: [...] }` in `data`/AJAX wrapper shape.
- API status rendering is read-only and must never break consumer form rendering.
- All error handling remains explicit and non-fatal, preserving editable form usability.
