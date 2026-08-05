# Release Notes

## 2026-07-11 Version 3.0.0 Status Snapshot
- Current release candidate: `.packages/WT Otpravkapochtaru_3.0.0.zip`.
- Package SHA-256: `B20137F93DBCDBA12F27D063FA569E8753537DF8FAE219BAB66AB1350F56C9E2`.
- Current git state: clean `main` tracking `origin/main`; local and remote resolve to `d1e24d6`.
- The latest pushed commit adds method-level documentation and explicit `ext-soap`/`ext-simplexml` Composer requirements: `d1e24d6`.
- Archive inspection confirms package manifest version `3.0.0`, package/plugin language files, plugin manifest, root `script.php`, no direct installer output, and Joomla message queue output via `$this->app->enqueueMessage($html, 'info')`.
- Verification boundary: configured QA passed before `d1e24d6` and archive verification is current as of 2026-07-11; live Joomla delivery/order evidence is from 2026-07-08; tracking remains blocked by missing SOAP tracking credentials in the installed plugin.

## 2026-07-11 HTTP And Filename Hardening

- Replaced deprecated CMS HTTP factory usage with `Joomla\Http\HttpFactory` for Joomla 5/6 compatibility and Joomla 7 forward readiness.
- Hardened downloaded document filename normalization against path traversal and invalid cross-platform names.
- Rebuilt package SHA-256: `C68F71A6F4D6B7E207CDF8684C11BFA457D7A1934C2D90BACC5DD1A767E22C1A`.
- No other audit findings were changed in this slice.

## Release Scope
- Close the `2026-04-22` public-surface cleanup cycle for the non-tracking `WT Otpravkapochtaru` facade.
- Ship the verified reduced surface proven on `joomla.local`.

## User-Visible Changes
- The non-tracking facade now exposes only live-justified methods.
- `getCountryList()` returns an official local reference dictionary instead of calling a dead live endpoint.
- Donor-stale methods without a live contract are no longer part of the public facade.

## Internal Changes
- `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php` keeps only the verified live mappings in the active public surface.
- `lib_webtolk_otpravkapochtaru/src/Exception/UnsupportedEndpointException.php` was removed because dead methods are no longer exposed publicly.
- `.webtolk/tmp/verify/joomla-local-api-sweep.php` now matches the real public facade and no longer carries deleted legacy calls.

## Verification Status
- Browser sweep on `joomla.local`: passed with `16 ok / 0 error / 14 skipped`.
- Verified methods include account/settings, reliability, tariff, country dictionary, postoffice, and read-only order/batch lookups.
- Remaining skipped methods are mutation-disabled operations in the read-only runner.

## Risks And Caveats
- This release intentionally narrows the public facade instead of preserving donor-era dead methods.
- Mutation paths remain disabled in the verification runner by default to avoid polluting the live account during routine assurance runs.

## Rollback Notes
- Revert the current facade and transport changes if the verified read-only surface regresses.
- Restore the previous library package on the Joomla stand only if you intentionally want the larger legacy facade back.

## Toolchain Contract References
- Browser execution via `http://joomla.local/tmp/wt_otpravkapochtaru_api_sweep.php`
- `php` lint for modified source files

## 2026-07-11 Repository-Only API Contract Appendix

- Added anonymized real REST response examples and observed JSON Schemas under `docs/api-schemas/otpravka/`.
- The appendix records a 29-request run for Saratov to Magadan and includes successful order edit/delete coverage.
- These documentation files are intentionally excluded from the Joomla release ZIP; archive inspection found 0 matching entries.
- No package rebuild was required for the repository-only documentation addition.

## 2026-07-11 Full Developer Documentation

- Added a complete Russian technical manual for all supported public methods.
- Added realistic examples with Joomla namespaces, client construction, normalization and error handling.
- Added separate references for entities and low-level REST/SOAP classes.
- Replaced the root quick start with a complete normalized tariff scenario.
- Documentation remains repository-only and is excluded from the Joomla installation archive.
- Package rebuilt with shared Phing target `3. Package release`; SHA-256 `6B5B7746D97FCECA754A4E237A4AE18B505AD9674F5D801FFD70615FAF30253F`.

## 2026-07-25 Package Rebuild

- Rebuilt `.packages/WT Otpravkapochtaru_3.0.0.zip` through the shared Phing target `3. Package release`.
- First archive inspection found an ignored `.playwright-mcp/` console log in the ZIP.
- Added `.playwright-mcp/` to `.webtolk/build/package.config.json` excludes and rebuilt again.
- Final package SHA-256: `D5AEE6C1373E5CE72CDFF531F6CA13274267FCCF0734E183CDE5B94AC193484E`.
- Final archive: 48 entries, package version `3.0.0`, `script.php` present, `composer.json` present, update server and changelog metadata present.
- Excluded path verification passed: no `.webtolk/`, `.packages/`, `docs/`, `.git/`, `.idea/`, `.serena/` or `.playwright-mcp/` entries.

## 2026-07-25 PHPDoc Cleanup Package

- Brought inspected class property and method docblocks closer to Joomla Coding Standards.
- No public API behavior, manifest metadata, language strings, database schema or remote CMS state changed.
- Rebuilt `.packages/WT Otpravkapochtaru_3.0.0.zip` through the shared Phing target `3. Package release`.
- Final package SHA-256: `FA4B34B34C826DDE56481D761FD280E7AE19E6C1D0D5DC43D60177CFD16EBF53`.
- Final archive: 46 entries; changed PHP files are present; `.phpunit.result.cache` and process/docs/cache directories are absent.
