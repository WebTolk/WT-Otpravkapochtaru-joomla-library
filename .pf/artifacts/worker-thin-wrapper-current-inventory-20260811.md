# Work Result: t18-current-library-inventory
run_id: lapaygroup-thin-wrapper-migration-20260811
project_root: .
mode: read-only planning
output_file: .pf/artifacts/worker-thin-wrapper-current-inventory-20260811.md

## Scope covered
- `lib_webtolk_otpravkapochtaru/src/**`
- `lib_webtolk_otpravkapochtaru/joomla.asset.json` (not present)
- library manifest context read only where needed (for namespace/classloader understanding)

## File/class inventory

| File | Class | Classification | Reason | Dependencies |
|---|---|---|---|---|
| `lib_webtolk_otpravkapochtaru/src/TrackingEntity.php` | `TrackingEntity` | `needs_decision` | SOAP-only tracking facade is isolated from REST calls and may or may not be covered by SDK tracking APIs. Needs confirmation of parity before removal/rewrite. | Joomla: none. Old fork: depends on `SoapRequest` and local `TrackingException`. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/SoapRequest.php` | `SoapRequest` | `replace_with_lapaygroup` | Legacy SOAP transport creator for tracking endpoints; not needed if SDK handling is used. | Joomla: none. Old fork: uses `CredentialsProvider`. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Service/LinkedSelectOptionsService.php` | `LinkedSelectOptionsService` | `keep` | Joomla field helper used by linked select fields; keeps form behavior and language map rendering. | Joomla: `Factory`, `Text`, `Registry`, `ArrayHelper`. Old fork: minimal. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Request.php` | `Request` | `replace_with_lapaygroup` | Legacy REST transport wrapper with hardcoded Otpravka endpoints and manual header/error logic. Replace with SDK transport path. | Joomla: `Joomla\Http\HttpFactory`, `Response`, `Uri`. Old fork: uses local `CredentialsProvider` + `TransportException`. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php` | `Otpravkapochtaru` | `rewrite_as_joomla_wrapper` | Main old facade; should become thin Joomla wrapper delegating to `LapayGroup\RussianPost` and exposing only needed plugin-facing methods. | Joomla: none. Old fork: uses local entities/exceptions and transport classes. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Dictionaries/CountryDictionary.php` | `CountryDictionary` | `needs_decision` | Static country list helper may be redundant if SDK already exposes supported country metadata. Needs parity check. | Joomla: none. Old fork: internal data class. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Configuration/CredentialsProvider.php` | `CredentialsProvider` | `rewrite_as_joomla_wrapper` | Plugin-settings bridge and auth token/user-key resolution is needed; likely kept as Joomla-oriented adapter with new provider mapping. | Joomla: `PluginHelper`, `Registry`. Old fork: throws local `ConfigurationException`. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Entity/AbstractEntity.php` | `AbstractEntity` | `replace_with_lapaygroup` | Generic hydration/normalization base for old DTO model; thin wrapper should avoid this old entity layer. | Joomla: none. Old fork: uses local `ValidationException`. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Entity/AddressReturn.php` | `AddressReturn` | `replace_with_lapaygroup` | Legacy return-shipment value object; replace with SDK DTOs or direct arrays. | Joomla: none. Old fork: extends `AbstractEntity`, uses `fromArray`/`toArray`. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Entity/CustomsDeclaration.php` | `CustomsDeclaration` | `replace_with_lapaygroup` | Legacy nested customs model likely duplicated by SDK request models. | Joomla: none. Old fork: extends `AbstractEntity`, uses nested `CustomsDeclarationItem`. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Entity/CustomsDeclarationItem.php` | `CustomsDeclarationItem` | `replace_with_lapaygroup` | Legacy customs-line model; remove from thin wrapper data layer. | Joomla: none. Old fork: extends `AbstractEntity`. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Entity/EcomData.php` | `EcomData` | `replace_with_lapaygroup` | Legacy e-commerce payload container; not needed with SDK-native structures. | Joomla: none. Old fork: extends `AbstractEntity`. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Entity/Item.php` | `Item` | `replace_with_lapaygroup` | Legacy nested cargo/item DTO in order payload path; replace through SDK models. | Joomla: none. Old fork: extends `AbstractEntity`. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Entity/Order.php` | `Order` | `replace_with_lapaygroup` | Core old DTO for order payload creation and validation logic; replace with SDK contract or array mapping. | Joomla: none. Old fork: extends `AbstractEntity`. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Entity/Recipient.php` | `Recipient` | `replace_with_lapaygroup` | Legacy recipient DTO; duplicate data modeling not needed in thin wrapper. | Joomla: none. Old fork: extends `AbstractEntity`. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Entity/ReturnShipment.php` | `ReturnShipment` | `replace_with_lapaygroup` | Legacy return-shipment DTO with nested hydration. Should be replaced by SDK-native request/response structures. | Joomla: none. Old fork: extends `AbstractEntity`, local `ValidationException`. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Exception/OtpravkapochtaruException.php` | `OtpravkapochtaruException` | `rewrite_as_joomla_wrapper` | Keep as package-local root for controlled exception translation at Joomla wrapper boundary. | Joomla: none. Old fork: local base throwable. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Exception/ConfigurationException.php` | `ConfigurationException` | `rewrite_as_joomla_wrapper` | Plugin-facing config/auth error type should remain as wrapper contract while delegating internal details to SDK. | Joomla: none. Old fork: extends local base exception. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Exception/TransportException.php` | `TransportException` | `rewrite_as_joomla_wrapper` | Plugin-facing transport error type should be retained as wrapper translation layer. | Joomla: none. Old fork: extends local base exception. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Exception/TrackingException.php` | `TrackingException` | `needs_decision` | Tracking exception path is tied to legacy SOAP flow; keep/remove depends on final tracking decision. | Joomla: none. Old fork: extends local base exception. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Exception/ValidationException.php` | `ValidationException` | `replace_with_lapaygroup` | Validation error class is tied to removed legacy entity hydration model. | Joomla: none. Old fork: extends local base exception. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php` | `LinkedSelectField` | `keep` | Core Joomla form field behavior for dependent selects, including JS wiring. | Joomla: `Factory`, `Form`, `ListField`. Old fork: none. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Fields/AccountinfoField.php` | `AccountinfoField` | `keep` | Joomla admin config info field for account status diagnostics. | Joomla: `NoteField`, `Text`, `Registry`. Old fork: uses `Otpravkapochtaru`, `CredentialsProvider`, local exceptions. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Fields/OpslistField.php` | `OpslistField` | `keep` | Joomla field for available ops list parameter and downstream linked options chain. | Joomla: `ListField`, `HTMLHelper`, `Text`, `PluginHelper`. Old fork: uses `Otpravkapochtaru` and local provider/exceptions. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Fields/MailtypesField.php` | `MailtypesField` | `keep` | Joomla field for linked mail-type dropdown in plugin settings and forms. | Joomla: `HTMLHelper`, `Text`, `PluginHelper`. Old fork: uses `Otpravkapochtaru`, `LinkedSelectOptionsService`, local exceptions/provider. LapayGroup: none currently. |
| `lib_webtolk_otpravkapochtaru/src/Fields/MailcategoriesField.php` | `MailcategoriesField` | `keep` | Joomla field for linked mail-category dropdown in plugin settings and forms. | Joomla: `HTMLHelper`, `Text`, `PluginHelper`. Old fork: uses `Otpravkapochtaru`, `LinkedSelectOptionsService`, local exceptions/provider. LapayGroup: none currently. |

## Files that should disappear from final thin wrapper
- `lib_webtolk_otpravkapochtaru/src/Request.php`
- `lib_webtolk_otpravkapochtaru/src/SoapRequest.php`
- `lib_webtolk_otpravkapochtaru/src/TrackingEntity.php`
- `lib_webtolk_otpravkapochtaru/src/Dictionaries/CountryDictionary.php`
- `lib_webtolk_otpravkapochtaru/src/Entity/AbstractEntity.php`
- `lib_webtolk_otpravkapochtaru/src/Entity/AddressReturn.php`
- `lib_webtolk_otpravkapochtaru/src/Entity/CustomsDeclaration.php`
- `lib_webtolk_otpravkapochtaru/src/Entity/CustomsDeclarationItem.php`
- `lib_webtolk_otpravkapochtaru/src/Entity/EcomData.php`
- `lib_webtolk_otpravkapochtaru/src/Entity/Item.php`
- `lib_webtolk_otpravkapochtaru/src/Entity/Order.php`
- `lib_webtolk_otpravkapochtaru/src/Entity/Recipient.php`
- `lib_webtolk_otpravkapochtaru/src/Entity/ReturnShipment.php`
- `lib_webtolk_otpravkapochtaru/src/Exception/ValidationException.php`

## Files that should remain because Joomla-specific
- `lib_webtolk_otpravkapochtaru/src/Configuration/CredentialsProvider.php`
- `lib_webtolk_otpravkapochtaru/src/Service/LinkedSelectOptionsService.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/AccountinfoField.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/OpslistField.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/MailtypesField.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/MailcategoriesField.php`

## Note
- `lib_webtolk_otpravkapochtaru/joomla.asset.json` is absent in this project tree.
- Read-only planning-only artifact; no production files modified.
