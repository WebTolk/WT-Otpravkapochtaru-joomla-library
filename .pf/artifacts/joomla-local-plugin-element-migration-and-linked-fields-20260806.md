# Joomla.local plugin element migration and linked fields check

- timestamp: 2026-08-06T13:54:28+04:00
- agent: Codex orchestrator
- scope: keep legacy system plugin element for seamless token migration, install new library element, adapt and verify JoomShopping shipping price form linked fields

## Knowledge Sources

- `.pf/AGENTS.md`
- `.pf/process-forge.yaml`
- `.pf/contexts/project-context.snapshot.md`
- Process Forge knowledge registry:
  - `docs.joomla-development-articles`
  - `docs.joomla-core.v6-1-2`
  - `docs.joomla-toolkit`
  - `docs.api.otpravka-pochta`
- Local Joomla source checked through the registered knowledge package root:
  - `D:\.agents\docs\joomla\core\Joomla-core\6.x\6.1.2\libraries\src\Form\FormField.php`
  - `D:\.agents\docs\joomla\core\Joomla-core\6.x\6.1.2\libraries\src\WebAsset\WebAssetRegistry.php`
  - `D:\.agents\docs\joomla\core\Joomla-core\6.x\6.1.2\libraries\src\WebAsset\WebAssetManager.php`

## Implementation Notes

- The system plugin extension element is kept as `wtotpravkapochtaru` so the existing Joomla `#__extensions.params` row keeps token settings.
- The library extension element remains the new `Webtolk/Otpravkapochtaru`; the old `Webtolk/Pochtaru` library was removed from `joomla.local`.
- `LinkedSelectField` now:
  - registers the plugin media asset package with Joomla WebAssetManager;
  - writes `data-wt-watchfield`, `data-wt-parentfield`, and `data-wt-url` through `FormField::$dataAttributes`, not late XML mutation;
  - appends one body-level fallback script tag for host forms where WAM assets are not emitted in time.
- `linked-select-fields.js` now:
  - is idempotent on repeated inclusion;
  - reads Joomla CSRF token name from `Joomla.getOptions('csrf.token')`, with the previous hidden-input fallback retained.

## Stand Evidence

- Package installed on `joomla.local` with Joomla CLI: OK.
- Redacted extension state:
  - package `pkg_lib_wt_otpravkapochtaru`, version `3.0.0`
  - system plugin `wtotpravkapochtaru`, version `3.0.0`, enabled, legacy params preserved:
    - `AccessToken`
    - `user_key_or_login_and_password`
    - `user_auth_key`
    - `user_login`
    - `user_password`
  - library `Webtolk/Otpravkapochtaru`, version `3.0.0`
- API credential probe through preserved plugin params:
  - `auth_mode=key`
  - `getShippingPoints ok=true`
  - returned shipping point count: `1`
- Package ZIP:
  - `.packages/WT Otpravkapochtaru_3.0.0.zip`
  - entries: `47`
  - contains `lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php`
  - contains `plg_system_wt_otpravkapochtaru/media/js/linked-select-fields.js`
  - contains `plg_system_wt_otpravkapochtaru/wtotpravkapochtaru.xml`

## Browser Evidence

Checked URL:

`http://joomla.local/administrator/index.php?option=com_jshopping&controller=shippingsprices&task=edit&sh_pr_method_id=1&shipping_id_back=`

Result:

- page status: `200`
- title: `Edit delivery methods prices - WebTolk Test local - Administration`
- unknown asset error: `false`
- linked JS included: `true`
- CSRF token name length from `Joomla.getOptions('csrf.token')`: `32`
- fields:
  - OPS: `sm_params_user_shipping_points`, value `109012`
  - mail types: `sm_params_user_available_mail_types`, `data-wt-watchfield=user_shipping_points`, AJAX action `getMailTypes`
  - mail categories: `sm_params_user_available_mail_category`, `data-wt-watchfield=user_shipping_points`, `data-wt-parentfield=user_available_mail_types`, AJAX action `getMailCategories`
- AJAX responses:
  - `getMailTypes`: `200`
  - `getMailCategories`: `200`
- Cascade:
  - OPS `109012` loaded 7 mail types
  - selected `POSTAL_PARCEL`
  - categories refreshed and retained 4 available categories
- Browser console:
  - no relevant JavaScript or AJAX errors after filtering local HTTP COOP warnings

## UX Update

- timestamp: 2026-08-06T14:50:54+04:00
- change:
  - dependent select fields are disabled while their AJAX request is in flight;
  - the previous disabled state is restored after options are redrawn or after an error fallback;
  - stale AJAX responses are ignored when a newer request for the same field is already active.
- implementation:
  - `linked-select-fields.js` assigns a per-field request id in `data-wt-request-id`;
  - `data-wt-loading=1` marks a field while fetching;
  - `data-wt-disabled-before-fetch` preserves the field's original disabled state.
- browser evidence with delayed `com_ajax`:
  - `getMailTypes`: `sm_params_user_available_mail_types` was `disabled=true` during fetch and `disabled=false` after redraw;
  - `getMailCategories`: `sm_params_user_available_mail_category` was `disabled=true` during fetch and `disabled=false` after redraw;
  - category options remained populated after selecting `POSTAL_PARCEL`.
- package/install:
  - release package rebuilt after the UX change;
  - package installed on `joomla.local` with Joomla CLI: OK.

## Language Constants For Linked Lists

- timestamp: 2026-08-06T15:07:05+04:00
- source:
  - local Process Forge package: `docs.api.otpravka-pochta`
  - local mirror root: `D:\.agents\docs\rest-api\otpravka-pochta`
  - official mirrored pages:
    - `raw\static\views\specification\enums-base-mail-type.html`
    - `raw\static\views\specification\enums-base-mail-category.html`
- change:
  - added `PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_MAIL_TYPE_*` constants for all official mail type codes used by the lists;
  - added `PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_MAIL_CATEGORY_*` constants for all official mail category codes used by the lists;
  - updated library `LinkedSelectOptionsService` to resolve labels through Joomla `Text::_()` and fall back to raw API code outside Joomla application contexts;
  - updated plugin `AjaxShippingOptionsService` to reuse library label resolution for `com_ajax` responses.
- files changed:
  - `lib_webtolk_otpravkapochtaru/src/Service/LinkedSelectOptionsService.php`
  - `plg_system_wt_otpravkapochtaru/src/Service/AjaxShippingOptionsService.php`
  - `plg_system_wt_otpravkapochtaru/language/ru-RU/plg_system_wtotpravkapochtaru.ini`
  - `plg_system_wt_otpravkapochtaru/language/ru-RU/plg_system_wt_otpravkapochtaru.ini`
  - `plg_system_wt_otpravkapochtaru/language/en-GB/plg_system_wtotpravkapochtaru.ini`
  - `plg_system_wt_otpravkapochtaru/language/en-GB/plg_system_wt_otpravkapochtaru.ini`
- stand evidence:
  - package rebuilt: `.packages/WT Otpravkapochtaru_3.0.0.zip`
  - package installed on `joomla.local` with Joomla CLI: OK
  - `ru-RU` installed language file parses and returns official labels, including:
    - `PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_MAIL_TYPE_EMS=Отправление EMS`
    - `PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_MAIL_TYPE_POSTAL_PARCEL=Посылка "нестандартная"`
    - `PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_MAIL_CATEGORY_ORDINARY=Обыкновенное`
  - browser check on the JoomShopping shipping price form confirms AJAX options are localized in the active admin language (`en-GB` on the stand):
    - `EMS -> EMS shipment`
    - `POSTAL_PARCEL -> Non-standard parcel`
    - `ORDINARY -> Ordinary`
    - `WITH_DECLARED_VALUE -> With declared value`

## WebAssetManager Audit

- timestamp: 2026-08-06T18:06:31+04:00
- local Joomla 6.1 sources checked:
  - `D:\.agents\docs\joomla\core\Joomla-6-1-docs\core-api-overview-docs\cms\webasset\web-asset-manager.md`
  - `D:\.agents\docs\joomla\core\Joomla-6-1-docs\core-api-overview-docs\cms\webasset\web-asset-registry.md`
  - `D:\.agents\docs\joomla\core\Joomla-6-1-docs\core-api-overview-docs\cms\document\scripts-renderer.md`
  - `D:\.agents\docs\joomla\core\Joomla-core\6.x\6.1.0\layouts\joomla\form\field\calendar.php`
  - `D:\.agents\docs\joomla\core\Joomla-core\6.x\6.1.0\libraries\src\HTML\Helpers\Select.php`
- finding:
  - `renderFallbackScript()` was a non-canonical body-level `<script>` fallback.
  - Joomla 6.1 core patterns use `WebAssetManager` from field/layout code, especially `registerAndUseScript()` or `useScript()`.
  - old direct script helpers are deprecated and must not be used.
- code change:
  - removed `renderFallbackScript()` and direct HTML script output from `LinkedSelectField`;
  - changed field script registration to `registerAndUseScript()` with dependency `core`;
  - added `core` dependency to `plg_system_wtotpravkapochtaru` asset metadata.
- runtime evidence:
  - package rebuilt and installed on `joomla.local`: OK;
  - with fallback removed, JoomShopping shipping price form renders field classes and data attributes, but no `linked-select-fields.js` script tag is present in final DOM;
  - linked selects stay at one `Select an option` option and the JS guard `window.WtOtpravkapochtaruLinkedSelectFieldsLoaded` is `false`.
- status:
  - code is closer to Joomla 6.1 canonical WebAssetManager usage;
  - runtime behavior on the current JoomShopping stand is not complete without an earlier asset-registration point or a host-form render-order fix.

### Local Documentation Follow-up

- timestamp: 2026-08-10T11:31:47+04:00
- scope: clarify task-local WebAssetManager findings against local ProcessForge Joomla documentation and Joomla 6.1.2 core.
- local sources checked:
  - `D:\.agents\processforge-workplace\packages\docs.joomla-6-1\indexes\resource-index.yaml`
  - `D:\.agents\processforge-workplace\packages\docs.joomla-development-articles\indexes\resource-index.yaml`
  - `D:\.agents\processforge-workplace\packages\docs.joomla-toolkit\indexes\resource-index.yaml`
  - `D:\.agents\docs\joomla\core\Joomla-6-1-docs\core-api-overview-docs-articles\cms\webasset\kak-podklyuchat-css-i-javascript-cherez-webassetmanager-v-joomla.md`
  - `D:\.agents\docs\joomla\core\Joomla-6-1-docs\core-api-overview-docs\cms\form\form-field.md`
  - `D:\.agents\docs\joomla\core\Joomla-6-1-docs\core-api-overview-docs\basics\joomla-internals\spravochnik-layoutov-yadra-joomla-6.md`
  - `D:\.agents\docs\joomla\core\joomla-toolkit\joomla-architecture-rules.md`
  - `D:\.agents\docs\joomla\core\Joomla-core\6.x\6.1.2\layouts\joomla\form\field\calendar.php`
  - `D:\.agents\docs\joomla\core\Joomla-core\6.x\6.1.2\layouts\joomla\form\field\subform\repeatable.php`
  - `D:\.agents\docs\joomla\core\Joomla-core\6.x\6.1.2\layouts\joomla\form\field\color\advanced.php`
- clarified findings:
  - Joomla 6.1 field-specific assets are commonly activated from the field layout itself when the layout is rendered before head compilation. Core examples include `calendar`, `subform` repeatable layouts, and advanced `color` field layout.
  - General form assets such as `form.validate` belong to the owning edit/form view, before `parent::display($tpl)`.
  - The local WebAssetManager article states that `WebAssetManager` is locked during head rendering; after this point, activating assets is too late.
  - For this task, the primary reusable contract remains: including/rendering `LinkedSelectField` must be enough for the field to activate the required web asset. JoomShopping-specific integration must not become the only path because the field can be used in other Joomla Form contexts.
  - The observed final DOM without `linked-select-fields.js` after fallback removal means the current JoomShopping shipping-price page may render that form too late for normal field-level WebAssetManager activation to reach the final head output. That is a host render-order/runtime problem to diagnose or compensate for, not a reason to remove self-loading responsibility from the field.

### JoomShopping Core Follow-up

- timestamp: 2026-08-10T11:45:00+04:00
- scope: inspect local JoomShopping core render path for the shipping price edit form and identify task-local asset-registration points.
- local sources checked:
  - `D:\.agents\processforge-workplace\packages\docs.joomshopping-core.v5-9-2\indexes\resource-index.yaml`
  - `D:\.agents\docs\joomla\Joomshopping-core\5.9.2\admin\Dispatcher\Dispatcher.php`
  - `D:\.agents\docs\joomla\Joomshopping-core\5.9.2\admin\Controller\ShippingspricesController.php`
  - `D:\.agents\docs\joomla\Joomshopping-core\5.9.2\admin\View\Shippingsprices\HtmlView.php`
  - `D:\.agents\docs\joomla\Joomshopping-core\5.9.2\admin\tmpl\shippingsprices\edit.php`
  - `D:\.agents\docs\joomla\Joomshopping-core\5.9.2\site\Lib\JSFactory.php`
  - `D:\.agents\docs\joomla\Joomshopping-core\5.9.2\site\addons\addon_core.php`
  - `D:\.agents\docs\joomla\Joomshopping-core\5.9.2\site\shippings\shippingext.php`
  - `D:\.agents\docs\joomla\Joomshopping-core\5.9.2\site\shippings\sm_standart_weight\sm_standart_weight.php`
  - `D:\.agents\docs\joomla\Joomshopping-core\5.9.2\site\shippings\sm_standart_weight\shippingpriceform.php`
- render path:
  - admin dispatcher `initShop()` imports `jshopping`, `jshoppingadmin`, and `jshoppingmenu` plugin groups, triggers `onAfterLoadShopParamsAdmin`, then registers core JoomShopping admin scripts/styles through `JSFactory::getWebAssetManager()`.
  - `ShippingspricesController::edit()` loads the shipping price row, resolves active shipping extensions with `JSFactory::getShippingExtList($actived)`, sets the `shippingsprices` view layout to `edit`, triggers `onBeforeEditShippingsPrices` with the view, then calls `$view->displayEdit()`.
  - `HtmlView::displayEdit()` only builds the toolbar and calls `parent::display($tpl)`.
  - `admin/tmpl/shippingsprices/edit.php` loops `$this->extensions` and calls `$extension->exec->showShippingPriceForm($row->getParams(), $extension, $this)` inside the form table.
  - `ShippingExtRoot` requires shipping extensions to implement `showShippingPriceForm()`. The stock `sm_standart_weight` implementation includes its local `shippingpriceform.php`.
  - `JSFactory::getShippingExtList()` includes each enabled shipping extension PHP file and creates `$row->exec = new $extname()`.
  - JoomShopping addon helper `addon_core.php` has `loadCss()` and `loadJs()` helpers that also activate assets through `JSFactory::getWebAssetManager()->registerAndUseStyle()` / `registerAndUseScript()`.
- task-local implications:
  - The baseline implementation should keep the reusable field self-loading its own asset from `getInput()`/field layout, matching Joomla core field patterns.
  - `onBeforeEditShippingsPrices` is the most targeted JoomShopping core event before the shipping price edit template renders extension forms, but it is only a JoomShopping-specific bridge if the host page's render order prevents field-level asset activation from being emitted.
  - `onAfterLoadShopParamsAdmin` is earlier but broader; it runs for JoomShopping admin after JoomShopping plugin groups are imported.
  - JoomShopping's own code path consistently activates component/addon assets through `JSFactory::getWebAssetManager()`, so a JoomShopping-native solution should stay on that path rather than returning body-level script tags from a shipping form.
  - Adding assets directly from `showShippingPriceForm()` is later in the same render path and may be too late for the observed stand behavior.
  - Because JoomShopping explicitly imports `jshoppingadmin`, a dedicated plugin in that group would be the most native receiver for `onBeforeEditShippingsPrices` if a JoomShopping bridge is needed. Reusing the current system plugin for that event needs runtime confirmation that the system plugin is registered for this legacy event on the JoomShopping admin request.

### Field Web Asset Implementation Follow-up

- timestamp: 2026-08-10T11:52:28+04:00
- scope: implement and prove linked field asset loading on `joomla.local` in the JoomShopping shipping-price addon form.
- code change:
  - `LinkedSelectField::setup()` now registers the required Joomla web asset as soon as the Joomla Form field is set up.
  - `LinkedSelectField::getInput()` keeps the field self-loading contract and returns the select markup with field metadata.
  - The primary path is still native Joomla WebAssetManager: `media/plg_system_wtotpravkapochtaru/joomla.asset.json` + `addExtensionRegistryFile('plg_system_wtotpravkapochtaru')` + `useScript('plg_system_wtotpravkapochtaru.linked-select-fields')`.
  - A one-time late loader is rendered by the field only as compatibility for hosts where the form HTML is emitted after normal document asset rendering; it does nothing if `linked-select-fields.js` is already present or the JS guard is already active.
- package and install evidence:
  - release package rebuilt: `.packages/WT Otpravkapochtaru_3.0.0.zip`
  - package entry count: `47`
  - package contains `lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php`, `plg_system_wt_otpravkapochtaru/media/joomla.asset.json`, and `plg_system_wt_otpravkapochtaru/media/js/linked-select-fields.js`
  - installed on `joomla.local` with `D:\OSPanel\modules\PHP-8.3\php.exe ...\cli\joomla.php extension:install`: OK
  - JoomShopping bridge plugin `System - WT JShop Otpravka.pochta.ru` enabled after install.
- browser runtime evidence:
  - page: `http://joomla.local/administrator/index.php?option=com_jshopping&controller=shippingsprices&task=edit&sh_pr_method_id=1&shipping_id_back=`
  - title: `Edit delivery methods prices - WebTolk Test local - Administration`
  - script present: `http://joomla.local/media/plg_system_wtotpravkapochtaru/js/linked-select-fields.js`
  - JS guard: `window.WtOtpravkapochtaruLinkedSelectFieldsLoaded === true`
  - linked fields present: `sm_params_user_available_mail_types`, `sm_params_user_available_mail_category`
  - `mailtypes` loaded 7 AJAX options for OPS `109012`; first values include `EMS`, `EMS_OPTIMAL`, `EMS_TENDER`, `ONLINE_COURIER`, `PARCEL_CLASS_1`
  - `mailcategories` loaded 4 AJAX options for selected type; values include `ORDINARY`, `WITH_DECLARED_VALUE`, `WITH_DECLARED_VALUE_AND_CASH_ON_DELIVERY`, `WITH_DECLARED_VALUE_AND_COMPULSORY_PAYMENT`
  - cascade check passed: changing `mailtypes` from `EMS` to `EMS_OPTIMAL` kept `mailcategories` populated with 4 options.

### Strict Field Layout Follow-up

- timestamp: 2026-08-10T12:27:30+04:00
- scope: test the hypothesis that linked select fields can connect their web asset from a custom Joomla Form field layout without body fallback or form-specific handlers.
- local Joomla core sources checked:
  - `D:\.agents\docs\joomla\core\Joomla-core\6.x\6.1.2\layouts\joomla\form\field\list.php`
  - `D:\.agents\docs\joomla\core\Joomla-core\6.x\6.1.2\libraries\src\Form\FormField.php`
  - `D:\.agents\docs\joomla\core\Joomla-core\6.x\6.1.2\layouts\joomla\form\field\calendar.php`
  - `D:\.agents\docs\joomla\core\Joomla-core\6.x\6.1.2\layouts\joomla\form\field\list-fancy-select.php`
  - `D:\.agents\docs\joomla\core\Joomla-core\6.x\6.1.2\layouts\joomla\form\field\media.php`
- implementation:
  - added `lib_webtolk_otpravkapochtaru/layouts/webtolk/otpravkapochtaru/form/field/linkedselect.php`;
  - added `<folder>layouts</folder>` to `lib_webtolk_otpravkapochtaru/otpravkapochtaru.xml`;
  - `LinkedSelectField` now uses layout `webtolk.otpravkapochtaru.form.field.linkedselect`;
  - `LinkedSelectField::getLayoutPaths()` adds `JPATH_LIBRARIES . '/Webtolk/Otpravkapochtaru/layouts'`;
  - removed the non-canonical fallback from the library field;
  - no form-specific `onContentPrepareForm` handler is present.
- layout finding:
  - standard Joomla `joomla.form.field.list` builds the `<select>` attributes and delegates rendering to `HTMLHelper::_('select.genericlist', ...)`;
  - core fields commonly activate WAM assets from field layouts, so the custom layout path is canonical for field-specific markup/assets.
- runtime evidence on `joomla.local` system plugin form:
  - package rebuilt: `.packages/WT Otpravkapochtaru_3.0.0.zip`;
  - package entry count: `48`;
  - package contains `lib_webtolk_otpravkapochtaru/layouts/webtolk/otpravkapochtaru/form/field/linkedselect.php`;
  - installed with `D:\OSPanel\modules\PHP-8.3\php.exe D:\OSPanel\home\joomla.local\public\cli\joomla.php extension:install --path=...`: OK;
  - page checked: `http://joomla.local/administrator/index.php?option=com_plugins&task=plugin.edit&extension_id=335`;
  - linked fields render as selects with `wt-linked-select-field`, `data-wt-requestfields`, and `data-wt-url`;
  - `linked-select-fields.js` is not present in final DOM scripts;
  - `window.WtOtpravkapochtaruLinkedSelectFieldsLoaded` is `false`;
  - dependent fields remain at the initial `Select an option` option.
- additional diagnostic evidence:
  - a temporary layout marker proved the custom layout was actually executed;
  - direct `registerAndUseScript()` from the layout still did not reach final DOM script output on this admin form;
  - temporary early system-plugin event experiments did not produce a working final architecture and were removed;
  - legacy plugin id `317` and new plugin id `335` are both present on the stand and share the same namespace, which is a separate local conflict risk for lifecycle-event diagnostics.
- status:
  - custom layout is implemented and packaged;
  - strict no-fallback runtime remains not complete on the tested Joomla admin form;
  - the evidence does not support relying on the field layout alone for this host form's external script output.

### Strict Field Layout Resolution

- timestamp: 2026-08-10T14:10:00+04:00
- scope: continue direct `joomla.local` stand debugging through PhpStorm MCP and Chrome DevTools after the layout was manually installed into Joomla's layout override path.
- corrected finding:
  - the custom field layout was executing before final script rendering;
  - `WebAssetManager` contained active asset `plg_system_wtotpravkapochtaru.linked-select-fields`;
  - the script was absent because the asset resolved to an empty URI, not because the layout path was inherently too late;
  - root cause: the asset registry used `plg_system_wtotpravkapochtaru/js/linked-select-fields.js`, while Joomla script asset URIs are resolved through the media `js` folder. Core examples use values like `com_fields/admin-field-edit.min.js`, with the physical file under `media/com_fields/js/`.
- code changes:
  - changed `plg_system_wt_otpravkapochtaru/media/joomla.asset.json` URI to `plg_system_wtotpravkapochtaru/linked-select-fields.js`;
  - changed the custom layout fallback registration URI to the same canonical value;
  - added package installer layout lifecycle: copy from installed library layout to `layouts/libraries/webtolk/otpravkapochtaru/form/field/linkedselect.php` on install/update, remove it on uninstall;
  - fixed package `postflight()` to enable current plugin element `wtotpravkapochtaru` instead of legacy `wt_otpravkapochtaru`.
- stand cleanup:
  - removed duplicate legacy system plugin `System - WT Otpravkapochtaru`, extension id `317`, through Joomla CLI;
  - verified remaining related extensions: package `pkg_lib_wt_otpravkapochtaru`, library `Webtolk/Otpravkapochtaru`, system plugin `wtotpravkapochtaru`, and JoomShopping package/plugin.
- package evidence:
  - rebuilt `.packages/WT Otpravkapochtaru_3.0.0.zip`;
  - package size: `117469` bytes;
  - package entry count: `48`;
  - package contains `script.php`, the library field layout, `plg_system_wt_otpravkapochtaru/media/joomla.asset.json`, and `plg_system_wt_otpravkapochtaru/media/js/linked-select-fields.js`;
  - installed on `joomla.local` with Joomla CLI: OK.
- browser evidence on system plugin form:
  - page: `http://joomla.local/administrator/index.php?option=com_plugins&task=plugin.edit&extension_id=335`;
  - script present: `http://joomla.local/media/plg_system_wtotpravkapochtaru/js/linked-select-fields.js`;
  - JS guard: `window.WtOtpravkapochtaruLinkedSelectFieldsLoaded === true`;
  - linked fields present and populated:
    - `jform_params_linked_test_mail_type`: `7` options;
    - `jform_params_linked_test_mail_category`: `4` options;
  - AJAX responses:
    - `getMailTypes`: `200`;
    - `getMailCategories`: `200`.
- browser evidence on JoomShopping shipping price form:
  - page: `http://joomla.local/administrator/index.php?option=com_jshopping&controller=shippingsprices&task=edit&sh_pr_method_id=1&shipping_id_back=`;
  - script present: `http://joomla.local/media/plg_system_wtotpravkapochtaru/js/linked-select-fields.js`;
  - JS guard: `window.WtOtpravkapochtaruLinkedSelectFieldsLoaded === true`;
  - linked fields present and populated:
    - `sm_params_user_available_mail_types`: value `EMS`, `7` options;
    - `sm_params_user_available_mail_category`: value `ORDINARY`, `4` options;
  - AJAX responses:
    - `getMailTypes`: `200`;
    - `getMailCategories`: `200`.
- QA:
  - `php -l script.php`: passed;
  - `php -l lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php`: passed;
  - `php -l lib_webtolk_otpravkapochtaru/layouts/webtolk/otpravkapochtaru/form/field/linkedselect.php`: passed;
  - `node --check plg_system_wt_otpravkapochtaru/media/js/linked-select-fields.js`: passed;
  - PHPUnit Unit suite: `OK (11 tests, 12 assertions)`.
- status:
  - strict no-fallback field layout path is now runtime-complete on both the neutral Joomla system plugin form and the JoomShopping shipping price form;
  - the reusable contract holds: rendering the field activates the field's required web asset through Joomla WebAssetManager.

### GetInput Web Asset Retest

- timestamp: 2026-08-10T14:32:00+04:00
- scope: verify whether the custom/system layout file can be removed and the linked-select asset can be activated directly from the Joomla Form field class.
- stand experiment:
  - removed `layouts/libraries/webtolk/otpravkapochtaru/form/field/linkedselect.php` from `joomla.local`;
  - removed the library source layout `libraries/Webtolk/Otpravkapochtaru/layouts/webtolk/otpravkapochtaru/form/field/linkedselect.php`;
  - changed installed `LinkedSelectField` to use Joomla's native `joomla.form.field.list` layout;
  - activated `plg_system_wtotpravkapochtaru.linked-select-fields` from `LinkedSelectField::getInput()` before `parent::getInput()`.
- runtime evidence:
  - system plugin form loaded `/media/plg_system_wtotpravkapochtaru/js/linked-select-fields.js`, JS guard was `true`, mail type had `7` options, mail category had `4` options;
  - JoomShopping shipping price form loaded `/media/plg_system_wtotpravkapochtaru/js/linked-select-fields.js`, JS guard was `true`, `sm_params_user_available_mail_types` had `7` options, `sm_params_user_available_mail_category` had `4` options;
  - `getMailTypes` and `getMailCategories` AJAX requests returned HTTP `200`.
- product decision:
  - removed the custom `linkedselect.php` layout from the package;
  - removed `<folder>layouts</folder>` from the library manifest;
  - removed installer copy/remove logic for the Joomla layout override path;
  - kept the canonical asset URI `plg_system_wtotpravkapochtaru/linked-select-fields.js`;
  - kept the current plugin element fix: `wtotpravkapochtaru`.
- package evidence:
  - rebuilt `.packages/WT Otpravkapochtaru_3.0.0.zip`;
  - package size: `115677` bytes;
  - package entry count: `47`;
  - package contains `LinkedSelectField.php`, `joomla.asset.json`, `linked-select-fields.js`, and `script.php`;
  - package contains no `linkedselect.php` and no linked layout entries;
  - installed on `joomla.local` with Joomla CLI: OK;
  - after install, PhpStorm MCP search found no `linkedselect.php` under `layouts/**` or `libraries/**`.
- QA:
  - `php -l lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php`: passed;
  - `php -l script.php`: passed;
  - `node --check plg_system_wt_otpravkapochtaru/media/js/linked-select-fields.js`: passed;
  - PHPUnit Unit suite: `OK (11 tests, 12 assertions)`.
- status:
  - layout copy is not needed;
  - the working product path is now field-owned WebAssetManager activation from `getInput()` plus Joomla's native list layout.

## QA

- `php -l` passed:
  - `lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php`
  - `lib_webtolk_otpravkapochtaru/src/Fields/MailtypesField.php`
  - `lib_webtolk_otpravkapochtaru/src/Fields/MailcategoriesField.php`
  - `lib_webtolk_otpravkapochtaru/src/Service/LinkedSelectOptionsService.php`
  - `plg_system_wt_otpravkapochtaru/src/Service/AjaxShippingOptionsService.php`
- `node --check plg_system_wt_otpravkapochtaru/media/js/linked-select-fields.js`: passed
- `phpcs` with project `phpcs.xml` on linked field PHP classes: passed
- PHPUnit:
  - command: `D:\.agents\tools\php-qa\vendor\bin\phpunit.bat --configuration D:\Dev\WT-Otpravkapochtaru-joomla-library\phpunit.xml --testsuite Unit`
  - result: `OK (11 tests, 12 assertions)`
  - focused linked list service tests:
    - `tests\Unit\Fields\LinkedSelectOptionsServiceTest.php`: `OK (4 tests, 4 assertions)`
    - `tests\Unit\PluginAjax\AjaxShippingOptionsServiceTest.php`: `OK (4 tests, 4 assertions)`
- 2026-08-10 final rerun:
  - `php -l lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php`: passed
  - `node --check plg_system_wt_otpravkapochtaru/media/js/linked-select-fields.js`: passed
  - `D:\.agents\tools\php-qa\vendor\bin\phpunit.bat -c phpunit.xml --testsuite Unit`: `OK (11 tests, 12 assertions)`
  - `phing -f phing.xml "3. Package release"`: passed

## Residual Risks

- The installed JoomShopping addon is a separate package already present on the stand; this repository delivers the reusable library field and system plugin assets it consumes.
- The repo worktree contains many untracked Process Forge artifacts and generated source files from the current feature work; this should be reviewed before commit packaging.

## 2026-08-10 Final No-Legacy Refactor

Status: passed. This section supersedes the intermediate custom-layout and legacy-AJAX notes above.

- Product code now keeps only the current contract:
  - Joomla Form fields consume `requestfields` JSON only;
  - no `watchfield` / `parentfield` fallback remains;
  - no old `onAjaxWt_otpravkapochtaru` event remains;
  - no `AjaxShippingOptionsService` layer remains;
  - no old `plg_system_wt_otpravkapochtaru.*` language files remain;
  - no custom `linkedselect.php` layout remains.
- `LinkedSelectField::getInput()` activates `plg_system_wtotpravkapochtaru.linked-select-fields` through Joomla WebAssetManager and renders with native `joomla.form.field.list`.
- `WtOtpravkapochtaru::handleAjaxRequest()` uses `LinkedSelectOptionsService` directly as the single option source.
- Test-stand JoomShopping addon XML was updated locally from `watchfield` / `parentfield` to:
  - mail type: `requestfields={"postoffice_code":"user_shipping_points"}`;
  - mail category: `requestfields={"postoffice_code":"user_shipping_points","mail_type":"user_available_mail_types"}`.
- Package evidence:
  - rebuilt `.packages/WT Otpravkapochtaru_3.0.0.zip`;
  - package size: `108292` bytes;
  - package entry count: `42`;
  - exact obsolete entries check: `0`;
  - contains `LinkedSelectField.php`, `MailtypesField.php`, `MailcategoriesField.php`, `LinkedSelectOptionsService.php`, `joomla.asset.json`, `linked-select-fields.js`, `WtOtpravkapochtaru.php`, `wtotpravkapochtaru.xml`, and `script.php`.
- Runtime evidence on `joomla.local` JoomShopping shipping price form:
  - `/media/plg_system_wtotpravkapochtaru/js/linked-select-fields.js` loaded with HTTP `200`;
  - JS guard `window.WtOtpravkapochtaruLinkedSelectFieldsLoaded === true`;
  - `data-wt-requestfields` present on both dependent fields;
  - OPS `109012` produced `7` mail type options and `4` mail category options for `EMS`;
  - `getMailTypes` and `getMailCategories` AJAX requests returned HTTP `200`.
- QA:
  - PHP syntax checks passed for linked fields, linked option service, and system plugin extension;
  - `node --check plg_system_wt_otpravkapochtaru/media/js/linked-select-fields.js`: passed;
  - project PHP lint script: passed;
  - PHPUnit Unit suite: `OK (7 tests, 8 assertions)`;
  - targeted `git diff --check` for product linked-select files: passed.
- Note:
  - A stale `AjaxShippingOptionsService.php` file left on `joomla.local` by previous non-public update testing was deleted manually from the test stand only. No public-release cleanup path was added to the product installer.

### 2026-08-10 Strict Options Follow-Up

- Removed `LinkedSelectField::ensureValueOption()`.
- Removed the two calls that appended the current saved value to mail type and mail category options.
- Resulting behavior: `MailtypesField` and `MailcategoriesField` now render only options produced by the current `LinkedSelectOptionsService` result or the explicit empty/error option branches.
- Checks:
  - `php -l` passed for `LinkedSelectField.php`, `MailtypesField.php`, and `MailcategoriesField.php`;
  - PHPCS passed for the three changed field files;
  - PHPUnit Unit suite: `OK (7 tests, 8 assertions)`;
  - targeted `git diff --check`: passed;
  - release ZIP rebuilt: `.packages/WT Otpravkapochtaru_3.0.0.zip`, `42` entries, `108036` bytes;
  - packaged `LinkedSelectField.php` does not contain `ensureValueOption`.

### 2026-08-10 Release Readiness Pass

- Release QA passed for PHP lint, PHPUnit, JS syntax, PHPCS, PHPStan, and PHP CS Fixer dry-run.
- Compatibility for existing system plugin parameter names is preserved by design: `AccessToken`, `user_key_or_login_and_password`, and `user_auth_key` continue to be read and remain in the system plugin form.
- The no-legacy decision applies to linked-select field attributes, field layout copy, and old AJAX event/service paths; it does not remove saved system plugin settings.
- Final package built through shared Phing packager:
  - `.packages/WT Otpravkapochtaru_3.0.0.zip`;
  - `41` entries;
  - `61599` bytes;
  - SHA-256 `670A7762C8789E22DE8D59BB35F66A131E9AD8510D7AF8D3DBE9CD76CA6313FA`;
  - forbidden entries check: `0`.
- Final package was installed on `joomla.local` through Joomla CLI successfully.
- JoomShopping shipping price form runtime after installing final ZIP:
  - linked-select asset HTTP `200`;
  - JS guard `true`;
  - `requestfields` present on both linked fields;
  - OPS `109012` produced `7` mail type options and `4` category options for `EMS`;
  - `getMailTypes` and `getMailCategories` returned HTTP `200`.
- Release hygiene:
  - `.gitignore` now excludes local `.codex/`, `.playwright-mcp/`, and `.pf/tmp/` diagnostics;
  - temporary diagnostic files are intentionally outside the release commit.
