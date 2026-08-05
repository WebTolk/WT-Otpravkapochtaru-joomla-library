# Browser Verification Report

## Current Checkpoint
- Date: `2026-04-24`
- Rebuilt package: `WT Otpravkapochtaru_0.1.0.zip`
- Reinstalled via administrator installer flow using authenticated HTTP requests that mirror the browser upload form
- Saved intermediate installer artifacts:
  - `.webtolk/tmp/verify/installer.html`
  - `.webtolk/tmp/verify/install-result.html`
- Confirmed installer success messages:
  - `Установка библиотеки успешно завершена.`
  - `Установка пакета WT Otpravkapochtaru завершена.`
- Current live extension ids after the reinstall:
  - system plugin: `268`
  - package: `269`
- Verified current administrator route:
  - `/administrator/index.php?option=com_plugins&task=plugin.edit&extension_id=268`
- Verified stale route diagnosis:
  - the previously reported failure used removed `extension_id=257`, which no longer exists after the clean reinstall

## Session Notes
- Firefox MCP restart still fails because the managed Firefox process closes before the session is usable.
- Chrome MCP is connected only to `about:blank`, so direct DOM-level browser driving was not available in this session.
- Final plugin-page validation was completed through authenticated administrator HTML inspection rather than MCP DOM control.

## Target Flow
- Open the installed `System - WT Otpravkapochtaru` plugin configuration page in Joomla administrator.
- Confirm that the library-driven `AccountinfoField` renders real account data and API connectivity details.

## Environment
- Local environment: `D:/OSPanel/home/joomla.local/public`
- Administrator URL: `http://joomla.local/administrator`
- Tested extension: `extension_id = 268`

## Steps Executed
1. Rebuilt the package and reinstalled it on `joomla.local`, first by clean CLI reinstall and then by authenticated administrator installer flow.
2. Attempted to use Firefox and Chrome MCP browser tools for direct administrator inspection.
3. Firefox MCP repeatedly failed to connect or start a managed browser session in this environment, and Chrome MCP exposed only `about:blank`.
4. Fell back to authenticated administrator HTML retrieval via local HTTP requests after logging in as `codex`.
5. Identified that the originally reported plugin-settings failure referenced stale `extension_id = 257`, which no longer exists after the clean reinstall.
6. Confirmed from the live plugins list and extension lookup that the current system plugin id is `268`.
7. Retrieved the plugin edit page HTML for `extension_id = 268` and verified that the route opens correctly without the earlier fatal path tied to the dead id.
8. Verified the rendered account info block from the installed package on the valid current route.

## Observed Behaviour
- The stale route using `extension_id=257` is invalid after the clean reinstall and is the source of the earlier reported Joomla warning path.
- The current route `extension_id=268` opens the WT Otpravkapochtaru plugin settings form correctly.
- The installed field rendered a card with real Russian Post account information.
- The HTML contained:
  - organization name `ФГУП "ПОЧТА РОССИИ"`
  - INN `7724261610`
  - KPP `772401001`
  - e-mail `test-test@test.ru`
  - agreement `Тестовое задание_МР от 2019-05-27`
  - ESPP code `144940`
  - success state `API подключен`
  - limit details `1000 / 2 / 998`

## Network Or Console Notes
- Browser MCP blocker:
  - `firefox-devtools/list_pages` and `firefox-devtools/new_page` timed out repeatedly after restart attempts.
  - `chrome-devtools` could not be used because the configured executable path did not exist in this environment.
- Installation blocker fixed during this cycle:
  - packaging initially shipped `.webtolk/tmp/dot-tmp/ff-profile-check/*`, which caused Joomla install cleanup to fail
  - excluding `.webtolk/tmp/dot-tmp/` from `.webtolk/build/package.config.json` resolved that issue

## Visual Or UX Findings
- The field output is now card-based, readable, and uses explicit success-state messaging instead of blank rendering.
- No broken raw donor strings or mojibake were observed in the installed admin HTML response.

## Verdict
- Package installation: **passed**
- Admin field rendering: **passed**
- Current plugin edit route (`extension_id=268`): **passed**
- Reported stale route (`extension_id=257`): **invalid after reinstall, not a product defect**
- MCP browser verification: **blocked by tool runtime**
- Fallback verification via authenticated admin HTML: **passed**

## Delivery And Order Runtime Check

### Current Checkpoint
- Date: `2026-07-08`
- Stand: `http://joomla.local`
- Script: `/tmp/wt_otpravkapochtaru_delivery_order_check.php`
- Source script: `.webtolk/tmp/verify/joomla-local-delivery-order-check.php`
- Dump root: `docs/dumps/delivery-order-check-20260708/`

### Scenario
- From: `410012 Саратов`
- To for tariff calculation: `455001 Магнитогорск`
- Test recipient: `Иванов Иван Иванович`
- Test phone: `+7 906 304-97-83`
- Test order address used for creation: `455001, Челябинская область, Магнитогорск, проспект Ленина, дом 1`
- Mail type/category: `POSTAL_PARCEL` / `ORDINARY`
- Mass: `1000`

### Steps Executed
1. Called Russian Post normalization endpoints before tariff calculation:
   - `/1.0/clean/address`
   - `/1.0/clean/physical`
   - `/1.0/clean/phone`
2. Called `getTariffAndDeliveryPeriod()` for `410012 -> 455001`.
3. Called the same normalization endpoints again before order creation.
4. Loaded shipping-point context from `getShippingPoints()`.
5. Called `createOrders()` with the normalized recipient/address/phone payload.

### Observed Behaviour
- Pre-tariff normalization: **passed**
- Delivery calculation: **passed**
  - `delivery-time.max-days`: `6`
  - `total-rate`: `40902`
  - `total-vat`: `8998`
- Pre-order normalization: **passed**
- Shipping-point lookup: **passed**
  - selected `postoffice-code`: `109012`
- Order creation: **passed**
  - `result-ids`: `2315788012`
  - `order-num`: `codex-delivery-order-20260708_093328`

### Notes
- The tariff calculation used the requested indexes exactly: `410012 -> 455001`.
- The address normalization for the concrete test address returned index `455039` for `проспект Ленина, дом 1`; the created order therefore used the normalized delivery address index.
- REST API credentials were available from the installed system plugin configuration on `joomla.local`.

### Verdict
- Delivery calculation: **passed**
- Order creation: **passed**
- Normalization before both operations: **passed**

## Tracking Runtime Check

### Current Checkpoint
- Date: `2026-07-08`
- Stand: `http://joomla.local`
- Script: `/tmp/wt_otpravkapochtaru_tracking_check.php`
- Source script: `.webtolk/tmp/verify/joomla-local-tracking-check.php`
- Dump root: `docs/dumps/tracking-check-20260708/`

### Source Tracking Number
- Latest order id: `2315788012`
- Latest order num: `codex-delivery-order-20260708_093328`
- Resolved barcode/RPO: `80092123913448`

### Steps Executed
1. Called `findOrderById(2315788012)`.
2. Called `findOrderByShopId('codex-delivery-order-20260708_093328')`.
3. Extracted `barcode` from both order lookup responses.
4. Called `getOperationsByRpo('80092123913448')`.
5. Called `getNpayInfo('80092123913448')`.
6. Called `getTickets(['80092123913448'])`.
7. Skipped `getOperationsByTicket()` because `getTickets()` returned no ticket.

### Observed Behaviour
- Order lookup by id: **passed**
- Order lookup by shop id: **passed**
- Barcode/RPO extraction: **passed**, `80092123913448`
- PHP SOAP extension in web runtime: **available**
- Installed plugin tracking credentials:
  - `tracking_login`: empty
  - `tracking_password`: empty
- `getOperationsByRpo()`: **blocked**, SOAP fault `Ошибка авторизации`
- `getNpayInfo()`: **blocked**, SOAP fault `Ошибка авторизации`
- `getTickets()`: **completed call**, returned no ticket and listed `80092123913448` under `not_create`
- `getOperationsByTicket()`: **skipped**, no ticket was returned

### Verdict
- Tracking number was read successfully from the latest test order: `80092123913448`.
- Tracking methods reached the Russian Post SOAP layer, but full success cannot be confirmed with the current installed configuration because tracking credentials are empty.
- This checkpoint is a configuration blocker, not a REST order lookup or PHP SOAP runtime blocker.
