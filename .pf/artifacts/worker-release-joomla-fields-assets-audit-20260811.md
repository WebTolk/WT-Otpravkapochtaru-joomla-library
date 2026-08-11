# t27-release-joomla-fields-assets-audit-20260811

verdict: pass

evidence:
- `lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php`: `LinkedSelectField` extends Joomla core `ListField` and declares `protected $layout = 'joomla.form.field.list'`, which is the native Joomla list field layout.
- `lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php`: `getInput()` calls a private `useLinkedSelectScript()` method that gets `Factory::getApplication()->getDocument()->getWebAssetManager()` and uses/registers script `lib_wt_otpravkapochtaru.linked-select-fields`.
- `lib_webtolk_otpravkapochtaru/media/joomla.asset.json`: asset owner and name are `lib_wt_otpravkapochtaru.linked-select-fields` with `uri` `lib_wt_otpravkapochtaru/js/linked-select-fields.js`, confirming library-owned WebAsset namespace.
- `lib_webtolk_otpravkapochtaru/otpravkapochtaru.xml`: library media block declares `<media folder="media" destination="lib_wt_otpravkapochtaru">` and includes `joomla.asset.json`, so the script installs under the library media destination.
- `plg_system_wt_otpravkapochtaru/wtotpravkapochtaru.xml`: plugin config fields `opslist`, `mailtypes`, `mailcategories` use `addfieldprefix="Webtolk\Otpravkapochtaru\Fields"` so these are shared library fields, not plugin-local duplicated fields.
- `lib_webtolk_otpravkapochtaru/src/Fields/OpslistField.php`, `MailtypesField.php`, `MailcategoriesField.php`: all classes are list-style option providers and do not include JoomShopping-specific coupling.
- `D:\.agents\docs\joomla\core\Joomla-core\6.x\6.0.4\libraries\src\Form\Field\ListField.php`: Joomla base `ListField` defines default `layout = 'joomla.form.field.list'` and `getInput()` uses renderer directly, matching the extension's layout use.
- `lib_webtolk_otpravkapochtaru/media/js/linked-select-fields.js`: exists in repository, matching the script URI in the asset descriptor.
- Search for commerce-variant terms (`joomshopping`, `joom-shop`, `jshopping`, `jshopping`) in the audited field/manifest scope returned no matches.

residual_risk:
- None in the scoped checks.
