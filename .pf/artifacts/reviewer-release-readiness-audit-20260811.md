# Release readiness review: release-readiness-audit-20260811

run_id: release-readiness-audit-20260811
assignment: t29-release-readiness-review
verdict: needs-fix

Findings by severity
- High: SOAP initialization is still hard-linked into core facade construction, so SOAP dependency behavior can block non-SOAP REST usage.
  Evidence:
  - `.pf/artifacts/worker-release-optional-soap-audit-20260811.md` points to `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php:55` showing `new TrackingEntity(new SoapRequest($credentialsProvider))` inside constructor while `Request.php` is REST-only.
  - Same file reports SOAP construction paths in `TrackingEntity`/`SoapRequest`, and that SOAP classes are initialized early instead of lazy-load.
  - This creates an unresolved product-code risk against the package's declared optional SOAP posture in `README.md` and `composer.json` (no ext-soap declared).

- Medium: Package archive / manifest consistency check is not green.
  Evidence:
  - `.pf/artifacts/worker-release-package-archive-audit-20260811.md` has verdict `needs-fix`.
  - The same report notes: `one or more required conditions are failing in archive or manifests; package may need rebuild or manifest correction`.

- Low: No blocker conditions were confirmed for these checks:
  - Old plugin-owned webassets being required was checked in package archive audit and reported as `False`.
  - `ext-soap` is not listed in composer runtime requirements per requirements audit.
  - Plugin field ownership shows shared library field implementation and no commerce-specific coupling in the checked scope.

Accepted evidence
- `.pf/artifacts/worker-release-requirements-audit-20260811.md`
  - composer and script requirements are aligned for PHP 8.3.0 and ext-mbstring/ext-simplexml.
  - `ext-soap` is not required in composer.
  - Joomla 6.1.2 docs in scope set minimum PHP `8.3.0`.
- `.pf/artifacts/worker-release-package-archive-audit-20260811.md`
  - Archive exists at `.packages/WT Otpravkapochtaru_3.0.0.zip` with `41` entries.
  - Old plugin-owned webassets are not present.
  - Library media/asset entries are present in zip.
- `.pf/artifacts/worker-release-joomla-fields-assets-audit-20260811.md`
  - Field and asset linkage is library-owned and plugin settings use shared library field namespace.

Evidence gaps (not confirmed blockers, but must be closed)
- No runtime execution evidence was provided for PHP without SOAP extension to prove/falsify fatal path on extension bootstrap.
- Package manifest failure details are not fully enumerated in the package audit output; exact missing condition(s) remain to be identified before merge.

Final release-readiness notes
- This slice **does not pass** review.
- Required fixes before release:
  1) Make SOAP objects lazy, or clearly gate SOAP facade initialization so missing ext-soap only affects tracking features.
  2) Resolve and clear package/archive manifest blockers from `t26` audit and rerun until that report is `pass`.
  3) Re-run this review after a verified runtime check on a SOAP-disabled environment and updated package artifact validation.
