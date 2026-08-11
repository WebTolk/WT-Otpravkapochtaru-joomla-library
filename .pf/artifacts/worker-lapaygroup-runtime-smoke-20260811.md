# Runtime Smoke Report

- run_id: `lapaygroup-russianpost-joomla-local-validation-20260811`
- task_id: `t09-lapaygroup-runtime-smoke`
- model: `gpt-5.3-codex-spark`
- artifact: `worker-lapaygroup-runtime-smoke-20260811.md`
- scope: read-only product code, no order creation/edit/delete

## Commands executed

1. `OtpravkaApi::settings()`
2. `OtpravkaApi::shippingPoints()`
3. One postoffice lookup required by current Joomla field chain
   - `searchPostOfficeByIndex(685000)`
4. One tariff calculation equivalent to current known route
   - `getTariff(27030, {"from-index":"410000","to-index":"685000","mail-type":"POSTAL_PARCEL","mail-category":"ORDINARY","mass":1000,"payment-method":"CASHLESS"})`

## Execution notes

- Initial run used default CLI PHP and failed during Joomla bootstrap:
  - `Joomla\\Database\\Exception\\UnsupportedAdapterException: The MySQLi extension is not available`
- Retried using `D:\OSPanel\modules\PHP-8.3\php.exe` (mysqli enabled).
- No product mutations were attempted and no secret values were printed.

## Result log (PASS/FAIL)

| Call | Status | Duration (ms) | Result shape | Count |
| --- | --- | ---: | --- | ---: |
| `OtpravkaApi::settings()` | FAIL | `2087` | `RuntimeException` | `n/a` |
| `OtpravkaApi::shippingPoints()` | FAIL | `2031` | `RuntimeException` | `n/a` |
| `searchPostOfficeByIndex(685000)` | FAIL | `2030` | `RuntimeException` | `n/a` |
| `getTariff(...)` | FAIL | `2027` | `RuntimeException` | `n/a` |

## Sanitized evidence

- All four calls reported the same transport-layer blocker:
  - `Failed to connect to otpravka-api.pochta.ru port 443 via 127.0.0.1`.
  - Exception class: `RuntimeException`.
  - No request/response payload was returned.

## Blockers

1. CLI runtime in OSPanel can initialize Joomla and use plugin credentials, but outbound API connectivity to `otpravka-api.pochta.ru` is blocked in this environment (`127.0.0.1` connect failure on port 443).
2. Because transport is blocked, smoke checks cannot produce API responses for the required methods in this run.

## Suggested next step

- Re-run this task on a host with direct outbound HTTPS access to `otpravka-api.pochta.ru`, then capture:
  - settings payload keys,
  - shipping-points total/count and selected fields,
  - postoffice lookup shape,
  - tariff result shape with pricing fields.
