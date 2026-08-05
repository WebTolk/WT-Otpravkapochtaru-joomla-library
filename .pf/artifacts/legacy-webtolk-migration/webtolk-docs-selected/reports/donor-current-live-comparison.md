# Donor Current Live Comparison

## Objective
Capture the final disposition of donor-era non-tracking methods after comparing donor code, current facade behavior, and live `joomla.local` verification.

## Sources
- Donor: `docs/lapay-group-russian-post-library/RussianPost-1.0.2/src/Providers/OtpravkaApi.php`
- Donor tariff provider: `docs/lapay-group-russian-post-library/RussianPost-1.0.2/src/Providers/Calculation.php`
- Current facade: `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`
- Live sweep baseline: `docs/dumps/api-sweep-20260422-joomla-local-remediation-05/index.json`
- Raw unsupported-endpoint probe: `.webtolk/tmp/verify/joomla-local-unsupported-probe.php`

## Final Disposition Matrix
| Method group | Donor state | Live state | Final disposition |
| --- | --- | --- | --- |
| `getAccountInfo`, `getSettings`, `getApiLimit` | donor-backed | live-confirmed | keep |
| `getShippingPoints` | donor-backed after path correction | live-confirmed | keep |
| `getRecipientReliability`, `getRecipientsReliability` | donor-backed | live-confirmed | keep |
| `getTariff`, `getTariffAndDeliveryPeriod` | donor transport stale, but product role valid | live-confirmed on `POST /1.0/tariff` | keep with current mapping |
| `getCountryList` | donor runtime endpoint stale | official catalog available locally | keep as local reference data |
| Postoffice lookup methods | donor-backed after base/path correction | live-confirmed | keep |
| `findOrderByShopId`, `getAllBatches` | donor-backed | live-confirmed in read-only mode | keep |
| `getBalance` | donor-backed | live endpoint absent | remove |
| `getCategoryList`, `getCategoryDescription`, `getObjectInfo` | donor-backed only through legacy delivery dictionary endpoint | live endpoint absent | remove |

## Product Rule
- Donor method presence alone is not enough to justify public API retention.
- If the live contract is absent and no stable local replacement is needed, the method should not remain in the public facade.

## Verified Cleanup Outcome
- Browser rerun on `2026-04-22` against `http://joomla.local/tmp/wt_otpravkapochtaru_api_sweep.php` confirms the cleanup baseline:
  - `16 ok`
  - `0 error`
  - `14 skipped`
- The verified non-tracking surface no longer carries dead donor-era methods as public API.
- Remaining skipped methods are mutation-disabled operations only.
