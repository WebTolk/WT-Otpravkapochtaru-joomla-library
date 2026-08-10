# Category/Type Source Contract

Date: 2026-08-06
Task: `t01c-category-type-source-contract`
Decision: implementation may proceed after orchestrator review

## MCP Evidence

Main orchestrator used Serena first for repository-local pattern search. Shell reads were used only after Serena identified the relevant legacy files and exact search targets.

Worker assignments still require MCP PHPStorm first. A worker may use shell fallback only if the launch prompt or an orchestrator review explicitly approves it before the run starts.

Runtime caveat: implementation workers must verify PHPStorm MCP availability in their own session before code edits. The canonical local endpoint for this project is `127.0.0.1:64442`.

## Authoritative Source

The old package implemented linked lists from Otpravka account data loaded through `/1.0/user-shipping-points`.

Current library method:

- `Webtolk\Otpravkapochtaru\Otpravkapochtaru::getShippingPoints()`
- endpoint: `/1.0/user-shipping-points`

Legacy method:

- `Otpravkapochtaru::getUserShippingPoints()`
- endpoint: `/1.0/user-shipping-points`

For implementation, use `getShippingPoints()` as the authoritative account-specific source for OPS data and nested availability data.

## Required Data Fields

Selected OPS object:

- `operator-postcode`
- `ops-address`
- `user-available-products`, if returned by API/client
- `user-available-mail-types`, optional sanity/filter source only

Product object:

- `mail-category`
- `mail-type`
- `product-type`, not needed for current select values

## Corrected Chain

The clarified chain is:

1. `user_shipping_points`
2. `user_available_mail_types`
3. `user_available_mail_category`

This matches the legacy JoomShopping addon behavior and replaces the earlier reversed assumption.

Type endpoint input:

- `postoffice_code`

Type endpoint output:

- unique type options from selected OPS `user-available-mail-types`
- if `user-available-mail-types` is missing, derive unique `mail-type` values from selected OPS `user-available-products[*].mail-type`

Category endpoint input:

- `postoffice_code`
- `mail_type`

Category endpoint output:

- unique category options from selected OPS `user-available-products[*]` where `mail-type == mail_type`, using each product's `mail-category`

## Fallback Rules

- If selected OPS is missing: return empty options and a safe user-level message.
- If `user-available-products` is missing or not an array: return empty options and a safe user-level message.
- Do not synthesize type/category pairs from the global static XML list.
- Do not use `user-available-mail-types` to satisfy the category endpoint, because categories are product-specific.
- Unknown enum codes may be returned as their raw code as label fallback, but must remain escaped by Joomla/JSON output handling.

## AJAX Contract

Use JSON only:

```json
{
  "success": true,
  "data": {
    "options": [
      {"value": "ORDINARY", "text": "Обыкновенное"}
    ]
  }
}
```

Errors must not include tokens, credentials, raw upstream response bodies, stack traces, or private local paths.

## Security Contract

- Action allow-list only.
- Accept only Joomla input API values.
- `postoffice_code`: strict Russian postal index shape, six digits.
- `mail_type`: validate against type enum map and selected OPS type list/products.
- `mail_category`: validate against category enum map and selected OPS products where needed.
- Reject unsupported action, malformed input, and method mismatch.
- Require Joomla session token for admin-originated field refresh requests unless a Joomla runtime check proves a different native `com_ajax` token mechanism is required.
- Check plugin credentials/config availability before calling the API.
- Return JSON through Joomla response facilities, not echoed raw HTML.

## Implementation Gate Decision

`GO`, with constraints:

1. Implementation must preserve the clarified `OPS -> type -> category` order.
2. Implementation must keep `watchfield`.
3. Implementation must use the field `url` attribute for relative `com_ajax` URLs.
4. Implementation must not copy the legacy raw HTML AJAX response style.
5. Implementation workers must include PHPStorm MCP evidence in their reports.
6. Implementation must use Joomla APIs for data handling where appropriate:
   - `Joomla\Registry\Registry` for object/array response traversal when it makes the API response safer and clearer;
   - `Joomla\Utilities\ArrayHelper` for array extraction/filtering where it fits;
   - `Joomla\CMS\HTML\HTMLHelper` for select options;
   - `Joomla\CMS\Language\Text` for labels/messages;
   - `Joomla\CMS\Session\Session` and Joomla response APIs for AJAX security/JSON output.
