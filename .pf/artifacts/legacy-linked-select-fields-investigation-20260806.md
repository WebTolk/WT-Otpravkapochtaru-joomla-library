# Legacy Linked Select Fields Investigation

Date: 2026-08-06
Scope: `.pf/tmp/pkg_smwtotpravkapochtaru_1.3.6/`
Mode: planning only, product code unchanged

## Files Checked

- `lib_webtolk/Otpravkapochtaru.php`
- `wtotpravkapochtaru/wtotpravkapochtaru.php`
- `sm_wt_otpravka_pochta_ru/components/com_jshopping/shippings/sm_wt_otpravka_pochta_ru/fields/wtlistops.php`
- `sm_wt_otpravka_pochta_ru/components/com_jshopping/shippings/sm_wt_otpravka_pochta_ru/js/shippingpriceformhelper.js`
- `sm_wt_otpravka_pochta_ru/components/com_jshopping/shippings/sm_wt_otpravka_pochta_ru/params.xml`
- `sm_wt_otpravka_pochta_ru/components/com_jshopping/shippings/sm_wt_otpravka_pochta_ru/shippingpriceform.php`

## Legacy Behavior

The old JoomShopping shipping method used three settings fields:

1. `user_shipping_points`
2. `user_available_mail_types`
3. `user_available_mail_category`

The old dynamic chain was:

1. On page load, load types for the selected OPS.
2. On `#sm_params_user_shipping_points` change, refresh `#sm_params_user_available_mail_types`.
3. On `#sm_params_user_available_mail_types` change, refresh `#sm_params_user_available_mail_category`.

This means the legacy order was `OPS -> type -> category`.

The new library task requires the order `OPS -> category -> type`, so the old code is a data-source and interaction reference, not a direct chain to copy.

## Legacy Data Source

The old OPS field called `Otpravkapochtaru::getUserShippingPoints()`, which requested `/1.0/user-shipping-points`.

The old type endpoint used:

- selected `postoffice_code`;
- matching shipping point by `operator-postcode`;
- nested `user-available-mail-types`;
- a static label map for mail type codes.

The old category endpoint used:

- selected `postoffice_code`;
- selected `mail_type`;
- matching shipping point by `operator-postcode`;
- nested `user-available-products`;
- each product's `mail-type`;
- each product's `mail-category`;
- a static label map for mail category codes.

## Legacy AJAX Shape

The old system plugin exposed `onAjaxWtotpravkapochtaru()`.

Actions:

- `getUserAvailableMailTypesByPostofficeCode`
- `getOptionsUserAvailableMailCategoriesByMailType`

Inputs:

- `postoffice_code`, read as `int`
- `mail_type`, read as `raw`

Response:

- raw HTML `<option>` strings
- `format=raw`

Security gaps in the old implementation:

- no Joomla session token check;
- no user/administrator authorization check;
- no strict action allow-list beyond if/elseif;
- no strict enum validation before using `mail_type`;
- raw HTML output;
- no stable JSON response contract;
- no explicit safe failure contract.

## Local API Documentation Cross-Check

The local Otpravka API package confirms `/1.0/user-shipping-points`.

The local `/settings-shipping_points` HTML shows `operator-postcode` and `ops-address` for shipping points. The local `/settings-user_settings` HTML documents:

- `available-mail-types`
- `available-products`
- `user-available-mail-types`
- `user-available-products`
- `mail-category`
- `mail-type`
- `product-type`

Because the old runtime expected `user-available-mail-types` and `user-available-products` on each shipping point, the implementation must be tolerant:

1. Prefer product/type arrays found on the selected shipping point returned by `getShippingPoints()`.
2. If the current API/client returns the same structures through account settings instead, use an adapter/service that can later add a fallback from `getSettings()` without changing field or AJAX contracts.
3. If no product source is available, return an empty option list with a safe message; do not guess unsupported categories/types.

## New Source Contract

For the requested `OPS -> category -> type` chain:

1. Category list for OPS:
   - load selected OPS;
   - read `user-available-products`;
   - collect unique `mail-category` values;
   - label them through a local enum label map.

2. Type list for OPS and category:
   - load selected OPS;
   - read `user-available-products`;
   - filter products by selected `mail-category`;
   - collect unique `mail-type` values;
   - label them through a local enum label map.

Do not use the legacy `user-available-mail-types` as the primary source for the type field in the new chain, because it cannot answer "types available for this category" by itself. It may only be used as a secondary sanity filter if present.

## Implementation Implications

- Keep `watchfield`.
- Add `url` for dependent fields.
- Add a way for the type field to also know the selected OPS, for example `parentfield="user_shipping_points"`.
- Use JSON responses, not raw HTML.
- Use vanilla JS and Joomla WebAssetManager.
- Preserve saved values during initial render with static/current fallback options, then refresh client-side.
- Do not copy legacy jQuery code directly.
- Do not expose raw API data or secrets in AJAX responses.
