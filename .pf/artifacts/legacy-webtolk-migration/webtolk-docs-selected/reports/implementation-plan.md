# Implementation Plan

## Objective
Finish the non-tracking facade cleanup by removing dead donor-era methods from the public surface and closing the cycle with a fresh verified baseline.

## Preconditions
- Live sweep artifacts exist in `docs/dumps/api-sweep-20260422-joomla-local-remediation-05/`.
- Donor comparison work already established that the removed methods have no confirmed live contract.
- `joomla.local` is available for browser execution of the read-only sweep wrapper.

## Current Known Gap
- The working live surface is already verified, but the public facade still carried four dead donor-era methods with no confirmed replacement contract.
- Those methods do not belong in a new library that is not constrained by donor backward compatibility.
- The read-only sweep had to be refreshed after pruning them so the baseline matched the real public surface.

## Change Slices
1. Public-surface pruning
   - Remove `getBalance()`, `getCategoryList()`, `getCategoryDescription()`, and `getObjectInfo()` from the facade.
   - Remove the now-unused `UnsupportedEndpointException`.
2. Verification runner cleanup
   - Remove calls to deleted methods from `.webtolk/tmp/verify/joomla-local-api-sweep.php`.
   - Keep the raw unsupported-endpoint probe only for direct legacy endpoint evidence.
3. Assurance rerun
   - Re-run `http://joomla.local/tmp/wt_otpravkapochtaru_api_sweep.php` against the installed stand.
   - Record the new baseline for the reduced public surface.
4. Cycle close
   - Update release, patch, evolution and `.webtolk` logs to reflect the new public API boundary.

## Execution Order
1. Remove dead donor-era methods from the facade and verification scripts.
2. Sync the updated library and sweep wrapper to `joomla.local`.
3. Re-run the read-only sweep.
4. Close the cycle with updated docs and logs.

## Current Verification Baseline
- Verified via browser request to `http://joomla.local/tmp/wt_otpravkapochtaru_api_sweep.php`.
- Dump set: `docs/dumps/api-sweep-20260422-joomla-local-remediation-05`
- Summary: `16 ok / 0 error / 14 skipped`
- `ok` methods:
  - `getAccountInfo`
  - `getShippingPoints`
  - `getApiLimit`
  - `getSettings`
  - `getRecipientReliability`
  - `getRecipientsReliability`
  - `getTariff`
  - `getTariffAndDeliveryPeriod`
  - `getCountryList`
  - `searchPostOfficeByIndex`
  - `searchPostOfficeByAddress`
  - `searchPostOfficeByCoordinates`
  - `getPostOfficeServices`
  - `getPostalCodesInLocality`
  - `findOrderByShopId`
  - `getAllBatches`
- Additional skipped methods are mutation-dependent by design in read-only mode.

## Verification Hooks
- Verify that the deleted dead methods are absent from the active public sweep surface.
- Verify that `getShippingPoints()` still returns live data.
- Verify that postoffice methods still pass browser probes with Cyrillic query values.
- Verify that the sweep runner is read-only by default and does not create new orders.
- Verify that the summary is `16 ok / 0 error / 14 skipped`.

## Rollback Considerations
- Restore the previous package only if you intentionally want the larger donor-shaped facade back.
- Keep the raw unsupported-endpoint probe available as evidence if the removed methods are ever reconsidered.
