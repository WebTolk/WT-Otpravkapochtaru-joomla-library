# Git Delivery Audit - 2026-08-11

## Scope

- Prepare commit and push for the LapayGroup thin-wrapper migration, Joomla runtime field fixes, package build tooling, and ProcessForge evidence.
- Verify ignore rules before staging so local build output, archives, and upstream SDK copies stay out of Git.

## Git Ignore Findings

- `.packages/` is ignored.
- `dist/` is ignored.
- `build/.tmp/` is ignored.
- `build/.stage/` is ignored.
- `.pf/tmp/` is ignored.
- `node_modules/` is ignored.
- `vendor/` ignores nested vendor directories too, including `lib_webtolk_otpravkapochtaru/src/libraries/vendor/`.

## Upstream SDK Boundary

- Local upstream SDK files exist only as generated build/runtime material under ignored paths.
- `git check-ignore` confirms `lib_webtolk_otpravkapochtaru/src/libraries/vendor/` is ignored by `.gitignore`.
- `git ls-files` found no tracked `lapaygroup/russianpost` SDK source files.
- `build/release.php` copies only `lapaygroup/russianpost/src` and generated `autoload.php` into the Joomla package stage/archive, not into the repository index.

## GitHub Actions

- `.github/workflows/release.yml` is a project workflow for this package.
- The workflow is adapted from the WT Max Composer build pattern, not a copied reference folder.
- The workflow pulls `lapaygroup/russianpost` through Composer during release build and publishes `dist/*.zip`.
- Search over `.github`, `build/release.php`, `.dist/build/package.config.json`, and `composer.json` found no WT Max names, local Joomla URLs, credentials, or absolute local paths in the workflow/build files.

## Final Verification Before Commit

- PHPUnit: `OK (15 tests, 57 assertions)`.
- PHPStan: no errors.
- PHP lint: changed Joomla field/transport PHP files have no syntax errors.
- JS syntax: `linked-select-fields.js` passed `node --check`.
- Local package build: `dist/WT-Otpravkapochtaru-Joomla-library_3.0.0.zip`.
- Package evidence: `98` entries, `122925` bytes, SHA-256 `35F3B46945AA840A58CD64C870208D9895F29B1B8AAE7CDD8FA1D50116A5FFA5`.

## Residual Note

- Local `package-from-lock` needs a Composer lock that contains upstream package `time`. The temporary path-based lock in `build/.tmp/local-composer/composer.lock` does not contain that field, so the final local archive smoke used `build/release.php package --version=3.0.0 --date=11.08.2026` after SDK preparation.
