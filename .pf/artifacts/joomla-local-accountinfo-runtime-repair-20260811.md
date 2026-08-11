# Joomla Local Account Info Runtime Repair - 2026-08-11

## Scope

- Incident URL: `http://joomla.local/administrator/index.php?option=com_plugins&view=plugin&layout=edit&extension_id=389`
- Reported symptom: plugin settings field `account_info` showed an API error.
- Agent/role: Codex / local Joomla runtime verification
- Product source changed: yes
- Local Joomla stand changed: yes, installed rebuilt package after verification

## Reproduction

Browser evidence on the plugin edit page:

- `account_info` rendered `API request error`
- detail: `От сервера Почты России при вызове метода settings пришел пустой ответ`
- linked shipping point field rendered `Shipping points list unavailable` with the same empty-response pattern for `user-shipping-points`

Fresh library smoke through Joomla bootstrap before the fix:

- `OtpravkaApi::settings()`: fail, HTTP code `200`, empty body reported by SDK
- `OtpravkaApi::shippingPoints()`: fail, HTTP code `200`, empty body reported by SDK
- postoffice lookup: fail, HTTP code `200`, empty body reported by SDK
- tariff probe: fail, HTTP code `400`, empty body reported by SDK

Direct sanitized API probe using the same stored plugin settings:

- `GET /1.0/settings`: HTTP `200`, non-empty JSON, `97` top-level keys
- `GET /1.0/user-shipping-points`: HTTP `200`, non-empty JSON, `1` item
- configured mode and forced key/login-password probes succeeded
- conclusion: credentials and the remote Otpravka API were working; the bug was local response-body handling

## Root Cause

`LapayGroup\RussianPost\Providers\OtpravkaApi::callApi()` reads the response body via:

```php
$response->getBody()->getContents();
```

Joomla HTTP returned a seekable PSR-7 response body with the stream cursor already at EOF. A local probe showed:

- status: `401` for dummy credentials
- body size: `58`
- `tell()` before read: `58`
- first `getContents()` length: `0`
- after `rewind()`, `getContents()` length: `58`

The SDK therefore interpreted valid non-empty HTTP responses as empty.

## Fix

Added `Webtolk\Otpravkapochtaru\Joomla\RewindingPsr18Client`, a PSR-18 client decorator that rewinds seekable response bodies before returning responses to the upstream SDK.

Updated `Webtolk\Otpravkapochtaru\Joomla\Psr18TransportFactory` to pass Joomla HTTP through that decorator.

Vendor SDK files were not modified.

## Verification

Repository checks:

- OSPanel PHP lint passed for:
  - `lib_webtolk_otpravkapochtaru/src/Joomla/RewindingPsr18Client.php`
  - `lib_webtolk_otpravkapochtaru/src/Joomla/Psr18TransportFactory.php`
  - `tests/Unit/Joomla/RewindingPsr18ClientTest.php`
- PHPUnit passed: `15 tests, 57 assertions`
- focused PHPCS passed for changed files
- focused PHP-CS-Fixer dry run passed for changed files
- PHPStan passed: no errors
- `git diff --check` passed; only existing CRLF warnings for unrelated `.pf` files were printed

Package and stand checks:

- Built `.packages/WT Otpravkapochtaru_3.0.0.zip`
- Archive entries: `65`
- Archive bytes: `210874`
- Archive SHA-256: `9FD3DCE870B1582D664B98A80930395D08CACCE8ECBA4FABD6599A43BC2C9019`
- Archive contains:
  - `lib_webtolk_otpravkapochtaru/src/Joomla/RewindingPsr18Client.php`
  - `lib_webtolk_otpravkapochtaru/src/Joomla/Psr18TransportFactory.php`
  - `lib_webtolk_otpravkapochtaru/media/joomla.asset.json`
- Joomla CLI install passed: `Extension installed successfully.`
- Installed stand files match repository hashes:
  - `Psr18TransportFactory.php`: `0322E62807F1...`
  - `RewindingPsr18Client.php`: `10EA15A48821...`

Runtime smoke after package install:

- `OtpravkaApi::settings()`: pass, array count `97`
- `OtpravkaApi::shippingPoints()`: pass, array count `1`
- postoffice lookup by index `685000`: pass, array count `16`
- tariff probe: fail with normal parsed API error `HTTP 400`; the previous empty-body failure is gone

Browser smoke after package install:

- Plugin edit page opens at `extension_id=389`
- `account_info` shows `API connected`
- no visible `API request error`
- no visible `пустой ответ`
- shipping point select is populated with `109012 - ул. Никольская, д.7-9, стр.4, г. Москва`
- browser console only shows the unrelated HTTP Cross-Origin-Opener-Policy warning

## Worker State

- No active `process-forge` / `shell-worker` / `.pf` worker process remained after verification.

## Verdict

The field failure was caused by response stream cursor position when using Joomla HTTP as a PSR-18 client for the LapayGroup SDK. Rewinding the response body before SDK parsing fixes account info and shipping point loading on the Joomla test stand.
