# Reviewer Artifact: t11-lapaygroup-test-plan-review

run_id: `lapaygroup-russianpost-joomla-local-validation-20260811`
task_id: `t11-lapaygroup-test-plan-review`
reviewed_at: `2026-08-11`

## status

`rejected`

This rejects migration readiness, not the read-only test-plan direction. The plan is structurally conservative, but the worker evidence does not prove the required upstream SDK path and should stop any product-code migration proposal.

## findings

1. **Critical - Gate 1 did not pass: `lapaygroup/russianpost:2.0.0` was not installed and its Joomla-way transport was never instantiated.**
   Evidence: T07 reports the Composer install/download probe as `FAIL` because CLI `openssl` is unavailable and class installation could not proceed (`worker-lapaygroup-stand-dependency-probe-20260811.md:42-50`). T08 then reports `LapayGroup\RussianPost\Http\Psr18Transport` as missing, `Instantiation status: NOT_ATTEMPTED`, and no controlled request (`worker-lapaygroup-joomla-psr-transport-prototype-20260811.md:18-37`). This does not prove the target architecture, even though Joomla PSR interfaces and Laminas factories are present.

2. **Critical - Gate 2 did not prove the upstream SDK against the live API.**
   Evidence: the T09 scratch runner instantiates the installed project facade `Webtolk\Otpravkapochtaru\Otpravkapochtaru`, not `LapayGroup\RussianPost\OtpravkaApi`. The report labels calls as `OtpravkaApi::*`, but all four calls were executed through the current product runtime and all failed before returning API data because outbound HTTPS was blocked (`worker-lapaygroup-runtime-smoke-20260811.md:20-44`). This evidence is useful for environment diagnosis only.

3. **High - The parity matrix is not complete enough for the Joomla package public surface.**
   Evidence: the current facade exposes additional public methods that are not explicitly covered by the matrix, including `getRecipientReliability()`, `getRecipientsReliability()`, `createReturnShipment()`, `createReturnShipments()`, `editReturnShipment()`, `deleteReturnShipment()`, and `getCountryList()` (`lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`). The matrix also marks its own basis as non-runtime-verified because the SDK was not installable (`worker-lapaygroup-data-parity-risk-matrix-20260811.md:6-8`). Gate 3 is therefore incomplete.

4. **High - PHP 8.3 is recognized but not resolved as a release gate.**
   Evidence: the plan correctly states PHP `>= 8.3` as a check and says adoption is safe only if PHP 8.3+ is accepted as the product minimum (`lapaygroup-russianpost-joomla-local-test-plan-20260811.md:68-80`, `252-260`). T07 confirms the stand PHP is `8.3.30` (`worker-lapaygroup-stand-dependency-probe-20260811.md:15-18`), but the release decision for dropping any lower supported baseline remains open. Migration must not proceed until that decision is explicit.

5. **Medium - Live API test scope is read-only, but the sanitizer is not strict enough for a successful rerun.**
   Evidence: the plan forbids order creation/mutation and raw credential printing (`lapaygroup-russianpost-joomla-local-test-plan-20260811.md:121-137`), and T09 only attempted settings, shipping-points, postoffice lookup, and tariff calls (`worker-lapaygroup-runtime-smoke-20260811.md:9-17`). Current artifacts inspected for this run do not contain raw authorization values. However, the smoke runner samples scalar response values on success, so it should be changed to an allowlist of non-secret keys, counts, and shape metadata before any real successful account response is published.

6. **Low - Product code remained read-only in the observed worktree.**
   Evidence: the plan forbids product paths and commits (`lapaygroup-russianpost-joomla-local-test-plan-20260811.md:26-43`), and `git status --short` showed no modifications under the forbidden product-code paths at review time. The only observed changes were Process Forge artifacts, run files, and existing orchestration log state.

7. **Low - One worker artifact includes a local absolute stand path.**
   This is not a migration blocker, but it violates the Process Forge public-artifact hygiene rule. Future public artifacts should use project-relative or sanitized stand references.

## missing-evidence

- Successful isolated installation or verified source availability for exact `lapaygroup/russianpost:2.0.0`.
- Actual construction of `LapayGroup\RussianPost\Http\Psr18Transport` with `Joomla\Http\Http` and Laminas Diactoros factories.
- Any controlled request through that transport.
- Any read-only live Russian Post response returned through the upstream SDK.
- Live response-shape evidence for `settings`, `shippingPoints`, postoffice lookup, and tariff using sanitized, allowlisted output.
- Explicit parity rows for recipient reliability, return shipments, country list, public entity input contracts, and binary document wrapper compatibility.
- A signed release decision that accepting `lapaygroup/russianpost` means raising the product/runtime minimum to PHP 8.3.

## migration-risk-classification

`needs-more-proof`

The plan itself is conservative and read-only, and Symfony HTTP Client is not used as the target path. The evidence is still insufficient: dependency installation failed, the target transport never existed in runtime, the live smoke used the current facade instead of upstream SDK, and the parity matrix is partially inferred.

## required-follow-ups

1. Fix the test environment first: enable CLI `openssl` for Composer or provide a verified, exact `lapaygroup/russianpost:2.0.0` vendor source in scratch.
2. Rerun T07 and require the isolated SDK install/download step to pass before T08/T09.
3. Rerun T08 and require real `Psr18Transport` instantiation with `Joomla\Http\Http`, `RequestFactory`, `StreamFactory`, and `UploadedFileFactory`, plus a harmless controlled request.
4. Rerun T09 with the LapayGroup SDK, not the current `Webtolk\Otpravkapochtaru` facade, after fixing outbound HTTPS/proxy access.
5. Replace result sampling with strict allowlisted shape/count/key reporting before any successful live account response is written.
6. Extend T10 to cover all public facade methods and public input/entity contracts, then add golden payload/response comparisons for high-risk adapters.
7. Record the PHP 8.3 baseline decision as an explicit release gate before any migration proposal.
