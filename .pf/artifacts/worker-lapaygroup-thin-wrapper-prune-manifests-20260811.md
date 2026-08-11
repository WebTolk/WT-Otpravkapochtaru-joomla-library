# Task Artifact: t36-fork-prune-manifest-writer

## Assignment
- run_id: `lapaygroup-thin-wrapper-implementation-20260811`
- task_id: `t36-fork-prune-manifest-writer`
- scope: remove forked SDK surface from product library and update Joomla manifests/installers for thin wrapper + packaged upstream SDK.

## Verdict
- **Status:** `DONE` (with known verification blockers)
- **Outcome:** forked runtime classes removed, manifests updated to thin-wrapper/package layout, version/date tokenization applied, required installer compatibility points preserved.

## Files Changed
- `lib_webtolk_otpravkapochtaru/otpravkapochtaru.xml`
  - version/date tokenized to `__DEPLOY_VERSION__` / `__DEPLOY_DATE__`
  - `<files>` section updated to include:
    - `<folder>src</folder>`
    - `<folder>src/libraries</folder>`
    - `<filename>otpravkapochtaru.xml</filename>`
- `pkg_lib_wt_otpravkapochtaru.xml`
  - version/date tokenized to `__DEPLOY_VERSION__` / `__DEPLOY_DATE__`
- `plg_system_wt_otpravkapochtaru/wtotpravkapochtaru.xml`
  - version/date tokenized to `__DEPLOY_VERSION__` / `__DEPLOY_DATE__`

## Files Deleted
- `lib_webtolk_otpravkapochtaru/src/Configuration/` (entire directory)
- `lib_webtolk_otpravkapochtaru/src/Dictionaries/` (entire directory)
- `lib_webtolk_otpravkapochtaru/src/Entity/` (entire directory)
- `lib_webtolk_otpravkapochtaru/src/Exception/` (entire directory)
- `lib_webtolk_otpravkapochtaru/src/Request.php`
- `lib_webtolk_otpravkapochtaru/src/SoapRequest.php`
- `lib_webtolk_otpravkapochtaru/src/TrackingEntity.php`
- `lib_webtolk_otpravkapochtaru/src/Transport/` (removed after becoming empty)

## Compatibility Notes
- Preserved existing plugin element and parameter names in `plg_system_wt_otpravkapochtaru/wtotpravkapochtaru.xml` (no renames, no setting migration required).
- Kept immutable directories/files required by assignment:
  - `src/Fields/**`
  - `src/Service/**`
  - `src/Otpravkapochtaru.php`
  - `src/Joomla/**`
  - `src/libraries/**`
- Preserved non-breaking installer behavior by not changing script logic:
  - legacy pre-3.0 library cleanup flow
  - plugin enablement path
  - Joomla/PHP version checks
  - non-blocking SOAP post-install/post-update warning
- Verified library wrapper is now thin and references upstream SDK path expectations by including `src/libraries` in library manifest.

## Commands Executed
- `php -l .\script.php` (failed: default PHP binary could not open input file due environment)
- `D:\OSPanel\modules\PHP-8.3\php.exe -l .\script.php` → `No syntax errors detected in .\script.php`
- `php build/release.php package --version=3.0.0 --date=08.07.2026`
  - failed as expected: `Prepared SDK tree is missing. Run php build/release.php prepare-sdk before packaging.`
- `Get-ChildItem lib_webtolk_otpravkapochtaru/src -Directory -Recurse` (cleanup verification)
- `Get-ChildItem lib_webtolk_otpravkapochtaru/src -Recurse -File` (cleanup verification)

## Verification Attempted
- PHPStorm MCP inspection calls were requested but blocked by tool-level cancellation (`user cancelled MCP tool call`) on edited PHP/XML inspection requests, so no MCP static-inspection evidence was produced.
- PHP syntax check completed successfully via OSPanel PHP binary.

## Residual Risks
- Build path currently cannot complete dry-run because prepared SDK source is missing (`build/.tmp/composer-vendor/lapaygroup/russianpost/src` absent); should run dependency preparation before packaging.
- Any stale runtime references in non-examined extension touchpoints could surface only after full staging/package smoke test; only the required file set was changed here.
- Because removal was broad in SDK internals, if any external consumers reference removed forked classes directly, runtime breakage is possible outside expected thin-wrapper integration points.
