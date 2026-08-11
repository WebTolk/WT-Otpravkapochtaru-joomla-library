# Thin wrapper architecture and build/install contract

## What this package is

- `WT Otpravkapochtaru` is implemented as a Joomla wrapper on top of the upstream SDK package `lapaygroup/russianpost`.
- Runtime behavior is exposed through `Webtolk\Otpravkapochtaru\Otpravkapochtaru` and uses `LapayGroup\RussianPost` classes/services under the hood.

## GitHub / Composer and package build

- `composer.json` declares:
  - `lapaygroup/russianpost`
  - `ext-soap`
  - `ext-zip`
  - PHP extension requirements required by runtime
- Release tooling stages SDK runtime from `build/.tmp/composer-vendor`.
- `build/release.php` copies SDK source from composer vendor into:
  - `lib_webtolk_otpravkapochtaru/src/libraries/vendor/lapaygroup/russianpost/src`
  - `lib_webtolk_otpravkapochtaru/src/libraries/vendor/autoload.php` (generated local bootstrap)
- Runtime façade includes the local SDK autoloader before service setup.

## ZIP-ready behavior and SOAP warning

- The release build writes ready ZIP files to `dist/*.zip`.
- Installer checks required extensions from runtime policy:
  - `mbstring` is required in installer preflight
  - SOAP is optional in installer policy
- If SOAP is unavailable, installer message includes warning block `PKG_LIB_WT_OTPRAVKAPOCHTARU_WARNING_OPTIONAL_SOAP_MISSING`.
- Runtime SOAP failures are still surfaced on SOAP-specific methods when used without proper SOAP setup.

## Joomla form fields and web assets

- Form fields and linked JS are now library-owned:
  - `lib_webtolk_otpravkapochtaru/src/Fields/*`
  - `lib_webtolk_otpravkapochtaru/media/joomla.asset.json`
  - `lib_webtolk_otpravkapochtaru/media/js/linked-select-fields.js`
- Plugin manifest no longer declares plugin-owned media for these assets.

## Current residual test coverage and risk notes

- Automated coverage added in `tests/Unit/Architecture/ThinWrapperContractTest.php` includes:
  - Composer dependency check (`lapaygroup/russianpost` and `ext-soap`)
  - Installer required extensions list excludes SOAP hard-fail
  - Release ZIP contains local SDK autoload and SDK directory entries
- Runtime/field source has no deleted fork namespace references.
- Remaining risk:
  - SOAP behavior and some response-level edge behavior are still validated by integration smoke and live stand workflows rather than exhaustive unit tests.
