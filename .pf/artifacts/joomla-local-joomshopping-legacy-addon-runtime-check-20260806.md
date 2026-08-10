# Joomla.local JoomShopping Legacy Addon Runtime Check

Date: 2026-08-06
Role: Codex orchestrator

## Scope

Check the legacy JoomShopping shipping method from `.pf/tmp/pkg_smwtotpravkapochtaru_1.3.6/` on `joomla.local` and confirm how the three related fields worked before the new library implementation.

## Stand State

- Joomla stand: `D:\OSPanel\home\joomla.local\public`
- JoomShopping source archive used: `JoomShopping-5.9.1.zip`
- Database backup before JoomShopping work: `.pf/tmp/joomla-local-db-backup-20260806-joomshopping`
- JoomShopping component state: installed enough for admin runtime; `com_jshopping` extension id `339`; 54 `#__jshopping_*` tables present.
- Legacy shipping method state: installed file extension id `333`; shipping calc row registered as `sm_wt_otpravka_pochta_ru`.

## Installer Findings

The JoomShopping 5.9.1 archive failed Joomla CLI install because its manifest references SQL paths at package root:

- `install/install.sql`
- `install/uninstall.sql`
- `sql/updates/mysql`

The archive stores those files under `admin/install/` and `admin/sql/updates/mysql/`. For the stand, the expanded installer copy under `.pf/tmp/installers/` was patched and the schema was imported manually. This was a stand setup workaround, not a repository product-code change.

## Legacy Addon Compatibility Workarounds On Stand

The legacy addon needed Joomla 6 compatibility fixes in the installed stand copy:

- `plugins/system/wtotpravkapochtaru/wtotpravkapochtaru.php`: `JPlugin` -> `Joomla\CMS\Plugin\CMSPlugin`
- `components/com_jshopping/shippings/sm_wt_otpravka_pochta_ru/const.php`: `Juri::root()` -> `Joomla\CMS\Uri\Uri::root()`
- `components/com_jshopping/shippings/sm_wt_otpravka_pochta_ru/fields/wtlistops.php`: `JFormFieldList` -> `Joomla\CMS\Form\Field\ListField`

These edits were applied only to the installed legacy package on `joomla.local`.

## Field Chain Confirmed

Legacy XML/JS/PHP confirms this order:

1. `user_shipping_points` / field id `sm_params_user_shipping_points` / label `ОПС`
2. `user_available_mail_types` / field id `sm_params_user_available_mail_types` / label `Тип отправления`
3. `user_available_mail_category` / field id `sm_params_user_available_mail_category` / label `Категория`

The legacy AJAX endpoints are:

- `action=getUserAvailableMailTypesByPostofficeCode`, input `postoffice_code`, output: mail types available for selected OPS.
- `action=getOptionsUserAvailableMailCategoriesByMailType`, inputs `postoffice_code` and `mail_type`, output: categories available for selected OPS and type.

Therefore the old working chain is `ОПС -> тип отправления -> категория`.

## Browser Evidence

After stand workarounds, the JoomShopping shipping price edit page opened:

`http://joomla.local/administrator/index.php?option=com_jshopping&controller=shippingsprices&task=edit&sh_pr_method_id=1&shipping_id_back=`

Detected fields:

- OPS select: `109012 - ул. Никольская, д.7-9, стр.4, г. Москва`
- Mail types select: 7 options, including `EMS`, `ONLINE_COURIER`, `EMS_OPTIMAL`, `SMALL_PACKET`, `EMS_TENDER`, `POSTAL_PARCEL`, `PARCEL_CLASS_1`
- Mail category select: 7 options initially

Manual interaction check:

- selected type changed to `POSTAL_PARCEL`
- `com_ajax` returned HTTP 200
- category select updated to category options for `POSTAL_PARCEL`

## API Check

The current installed new library call was also checked through Joomla plugin settings:

- class: `Webtolk\Otpravkapochtaru\Otpravkapochtaru`
- method: `getShippingPoints()`
- `auth_mode=key`
- access token length: 32
- user key length: 40
- result: success, array with 1 shipping point

Secrets were not printed.

## Risks For New Implementation

- The old AJAX endpoint accepts POST without an explicit token and returns raw HTML. New endpoints should keep Joomla token validation and strict action/input validation.
- Old AJAX output can be polluted by PHP warnings (`Deprecated` notice from old library). New endpoints must avoid warning output in response bodies and return clean Joomla-aware responses.
- Old category responses can contain duplicate options; new service should normalize/deduplicate where appropriate.
- Consumer fields should use `watchfield` and `url` attributes, but the runtime must treat them as form integration metadata only. Server-side AJAX must not trust field names or client URLs for authorization.
