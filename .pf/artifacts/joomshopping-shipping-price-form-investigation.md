# Investigation: JoomShopping shipping price form fields

## Metadata

- created_at: 2026-08-05T21:57:02+04:00
- scope: `.pf/tmp/pkg_smwtotpravkapochtaru_1.3.6/sm_wt_otpravka_pochta_ru`
- status: research-only
- product_code_changed: no

## Subject

The inspected package is a JoomShopping shipping addon. Its shipping price form contains three related fields:

- `user_shipping_points` - post office / shipment point list.
- `user_available_mail_types` - mail type list.
- `user_available_mail_category` - mail category list.

## Current Implementation

JoomShopping core calls the addon hook from the shipping price edit screen:

- JoomShopping admin template calls `showShippingPriceForm($row->getParams(), $extension, $this)`.
- The addon method `showShippingPriceForm(...)` includes `components/com_jshopping/shippings/sm_wt_otpravka_pochta_ru/shippingpriceform.php`.

The addon form is already based on Joomla Form:

- `shippingpriceform.php` imports `Joomla\CMS\Form\Form`.
- It creates the form with `Form::getInstance("sm_params", __DIR__ . "/params.xml", ["control" => "sm_params"])`.
- It binds saved shipping price params into the form.
- It renders the three target fields with `renderField(...)`.

The field definitions are in `components/com_jshopping/shippings/sm_wt_otpravka_pochta_ru/params.xml`:

- `user_shipping_points` uses custom field type `wtlistops` and `addfieldpath`.
- `user_available_mail_types` uses standard Joomla Form `list` with static XML options.
- `user_available_mail_category` uses standard Joomla Form `list` with static XML options.

The custom post office field is implemented in `components/com_jshopping/shippings/sm_wt_otpravka_pochta_ru/fields/wtlistops.php`:

- Class: `JFormFieldWtlistops`.
- Parent: `JFormFieldList`.
- `$type = 'wtlistops'`.
- `getOptions()` calls `Webtolk\Pochtaru\Otpravkapochtaru::getUserShippingPoints()`.
- Each option value is `operator-postcode`; each option label combines postcode and `ops-address`.

Dependent select behavior is implemented in JavaScript:

- `components/com_jshopping/shippings/sm_wt_otpravka_pochta_ru/js/shippingpriceformhelper.js`
- On page load it refreshes mail types by selected post office.
- On post office change it requests available mail types through `com_ajax`.
- On mail type change it requests available categories through `com_ajax`.
- The JS replaces `<option>` markup in the two dependent selects.

The XML deliberately contains a full static option set for mail types and categories. The JS comments explain that this keeps saved values visible before AJAX replacement on initial page load.

## Save And Runtime Path

The form uses `control => "sm_params"`, so submitted fields are named as:

- `sm_params[user_shipping_points]`
- `sm_params[user_available_mail_types]`
- `sm_params[user_available_mail_category]`

JoomShopping saves `post['sm_params']` into shipping method price params. The addon later reads the saved values from `$params` during shipping price calculation and maps them to the Russian Post tariff payload:

- `index-from`
- `mail-type`
- `mail-category`

## Joomla Form Modernization Direction

The target approach for Joomla 5/6 is to keep the Joomla Form-based storage contract and move the fields to modern namespaced FormField classes:

```xml
<form addfieldprefix="Vendor\\Extension\\Administrator\\Field">
  <fieldset name="shipping_prices">
    <field name="user_shipping_points" type="Ops" label="OPS" />
    <field name="user_available_mail_types" type="MailTypes" label="Mail type" />
    <field name="user_available_mail_category" type="MailCategories" label="Mail category" />
  </fieldset>
</form>
```

Recommended field model:

- `OpsField extends Joomla\CMS\Form\Field\ListField`
- `MailTypesField extends Joomla\CMS\Form\Field\ListField`
- `MailCategoriesField extends Joomla\CMS\Form\Field\ListField`

Each field should implement `getOptions()` and use a project service or library facade instead of inline request logic.

## Dynamic Dependency Boundary

Joomla Form can render the initial field state, but browser-side dependencies still need a runtime update path. For this addon there are two viable choices:

- Keep AJAX updates for dependent selects, but modernize them through Joomla WebAssetManager, Joomla input APIs, CSRF token handling, and structured JSON responses.
- Avoid dynamic select updates and render broader static lists, then validate incompatible combinations server-side. This is simpler but weaker for administrator UX.

The first option is the better fit because the current addon already depends on post-office-specific available mail types and mail categories.

## Risks Noted For Implementation

- The current custom field uses legacy `JFormFieldList` style. Joomla 5/6 code should use namespaced `Joomla\CMS\Form\Field\ListField`.
- Saved JS values are emitted inline and should be JSON-encoded when modernized.
- AJAX handlers referenced by the shipping addon are not part of the inspected shipping addon package itself; they are expected from the related system plugin / library layer.
- Current dependent select update builds option HTML. JSON response plus controlled rendering would be cleaner, but HTML option replacement is a lower-risk compatibility path.

## Next Implementation Slice

1. Create modern namespaced Joomla Form field classes for OPS, mail types, and mail categories.
2. Keep `control => "sm_params"` so JoomShopping saving remains unchanged.
3. Move inline/dedicated JS into an asset-managed script.
4. Verify admin shipping price form rendering and saved params on a Joomla/JoomShopping stand.
