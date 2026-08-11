# Orchestrator Review: Release Readiness Worker Audit

Run: `release-readiness-audit-20260811`
Reviewer: main orchestrator
Product code changed: no

## Worker Execution

Launched Process Forge shell-workers with `gpt-5.3-codex-spark`:

- T25 `t25-release-requirements-audit`
- T26 `t26-release-package-archive-audit`
- T27 `t27-release-joomla-fields-assets-audit`
- T28 `t28-release-optional-soap-audit`
- T29 `t29-release-readiness-review`

All worker heartbeat files completed with `exit_code: 0`.

## Worker Reports

- T25: `.pf/artifacts/worker-release-requirements-audit-20260811.md`
- T26: `.pf/artifacts/worker-release-package-archive-audit-20260811.md`
- T27: `.pf/artifacts/worker-release-joomla-fields-assets-audit-20260811.md`
- T28: `.pf/artifacts/worker-release-optional-soap-audit-20260811.md`
- T29: `.pf/artifacts/reviewer-release-readiness-audit-20260811.md`

## Accepted Findings

- T25 passed:
  - package metadata is aligned on PHP `>=8.3.0`;
  - `ext-mbstring` and `ext-simplexml` are required;
  - `ext-soap` is not package-required;
  - installer preflight checks PHP `8.3.0` and `mbstring`;
  - README describes SOAP as optional for tracking only.
- T27 passed:
  - linked select fields use the native Joomla list layout;
  - the field activates `lib_wt_otpravkapochtaru.linked-select-fields`;
  - asset ownership is library-owned;
  - audited fields remain generic and are not JoomShopping-specific.
- T28 correctly identified a design smell:
  - `Otpravkapochtaru::__construct()` eagerly creates `TrackingEntity(new SoapRequest(...))`;
  - SOAP work itself is still in tracking paths.

## Corrected Worker Findings

T26 verdict `needs-fix` is rejected as a worker false positive.

Reason:

- The worker parsed the first manifest `<folder>src</folder>` as if it were the
  library media folder.
- Direct XML inspection shows the actual media block is:
  - media source folder: `media`;
  - media installed folder: `js`;
  - destination: `lib_wt_otpravkapochtaru`;
  - asset manifest: `joomla.asset.json`.
- ZIP inspection confirms:
  - archive exists;
  - size: `62097` bytes;
  - entries: `41`;
  - contains `lib_webtolk_otpravkapochtaru/media/joomla.asset.json`;
  - contains `lib_webtolk_otpravkapochtaru/media/js/linked-select-fields.js`;
  - contains no `plg_system_wt_otpravkapochtaru/media/*` entries.

T28 verdict `needs-fix` is partially accepted and narrowed.

Reason:

- Worker concern about eager SOAP initialization is valid as an architecture smell.
- It is not confirmed as a REST-only fatal blocker.
- Local CLI PHP 8.3.30 has no `soap` extension loaded.
- Probe `.pf/tmp/soap_optional_probe.php` showed:
  - `new Otpravkapochtaru(...)` succeeds without `ext-soap`;
  - calling `SoapRequest::createSingleClient()` fails only on the SOAP path with
    `Error: Undefined constant "Webtolk\Otpravkapochtaru\SOAP_1_2"`.

## Orchestrator Verdict

Status: `needs-follow-up`, not a package/release metadata blocker.

Release-readiness for non-tracking REST features is acceptable in the audited
scope:

- calculation/order paths are not proven to require `ext-soap`;
- package metadata no longer requires `ext-soap`;
- library-owned field assets are packaged correctly.

Recommended follow-up before public release:

- Add a tracking-only runtime guard for missing `ext-soap` so users get a clear
  domain/configuration exception instead of a raw PHP `Error` when they call SOAP
  tracking methods without the extension.
- Re-run a focused SOAP-disabled smoke after that guard is implemented.

No worker changed product code.

## Follow-Up Applied: Composer SOAP And Installer Warning

Status: product code updated by orchestrator after worker review.

- `ext-soap` was restored to `composer.json` because GitHub/Composer build workflows must have SOAP available.
- Joomla installer preflight still does not require SOAP.
- The post-install/post-update HTML message now includes a warning when `extension_loaded('soap')` is false:
  tracking methods will not work until SOAP is enabled.
- This keeps REST delivery calculation and order creation installable from the ready Joomla ZIP on hosts without SOAP, while keeping the build dependency explicit.

Verification:

- PHP lint for `script.php`: passed.
- Corrected-file whitespace check: passed.
- Release package build: passed.
- Built archive: `.packages/WT Otpravkapochtaru_3.0.0.zip`, 41 entries, 62326 bytes.
- ZIP inspection confirmed the installer warning hook and both localized warning strings are packaged.
- Joomla local install under OSPanel PHP 8.3 with SOAP omitted from CLI extensions: passed.
- Installer-message probe: warning appears when SOAP is absent and does not appear when SOAP is loaded.
