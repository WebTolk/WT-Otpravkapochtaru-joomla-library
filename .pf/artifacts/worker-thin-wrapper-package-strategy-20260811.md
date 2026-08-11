# Worker Artifact: t22-wrapper-package-strategy
run_id: lapaygroup-thin-wrapper-migration-20260811
task_id: t22-wrapper-package-strategy
mode: read-only planning

## Goal
Use `lapaygroup/russianpost` 2.0.0 as a thin runtime dependency of the Joomla package via Composer-in-CI refresh, package-local SDK copy, and package-local autoloading, without keeping a legacy/public facade compatibility layer and without adding JoomShopping integration.

## 1) SDK refresh during GitHub release
- Add `lapaygroup/russianpost` to `composer.json` root requirements (or `composer update` with explicit version pin on 2.0.0 in release workflow).
- Add a release workflow (`.github/workflows/release.yml`) that performs:
  - `composer config --no-plugins allow-plugins.composer/installers true` (if required by environment)
  - `composer update --with-dependencies --no-interaction` from project root to produce a fresh `composer.lock`.
- Keep Composer install output outside committed package content, e.g. `composer config -g --no-plugins vendor-dir build/.tmp/composer-vendor` (or env var `COMPOSER_VENDOR_DIR` for the command), matching WT Max pattern.
- Add/extend `build/release.php` to read dependency versions from `composer.lock` and stage release assets from the refreshed vendor tree.

## 2) Shipping SDK in the Joomla package
- In release assembly, copy the dependency runtime under a package-local runtime path, e.g.:
  - `lib_webtolk_otpravkapochtaru/src/libraries/lapaygroup/russianpost/...`
- Copy only what SDK runtime needs (source plus transitive runtime deps if not guaranteed by Joomla core/vendor) into the package tree; do not vendor a fixed, manually frozen snapshot.
- Generate a package-local autoloader in the package (e.g. `lib_webtolk_otpravkapochtaru/src/libraries/autoload.php`) and register:
  - `LapayGroup\\RussianPost\\ => src/libraries/lapaygroup/russianpost/src`
  - any transitive namespaces required by the shipped dependency set.
- Load local SDK autoloader in `Webtolk\Otpravkapochtaru` integration entry point before first SDK call.
- Do not modify Joomla core autoload stack or global `libraries/vendor` in normal package installs.

## 3) Package version/date derivation from Composer metadata
- Derive package values from the generated `composer.lock` entry for `lapaygroup/russianpost`:
  - `version` -> package build version token default (or package override).
  - `time` -> canonical build/deploy date if available, converted to package format.
- Keep a manual override path (read from workflow input/env):
  - `SDK_VERSION` and `SDK_RELEASE_DATE` (or equivalent package config overrides) for controlled emergency releases.
- Keep existing package metadata replacement mechanism (`.dist/build/package.config.json`) but drive values from locked composer metadata first, then apply explicit overrides.

## 4) Minimal wrapper/services in our namespace
- Keep only thin adapters in `Webtolk\Otpravkapochtaru\Sdk`:
  - `LapayGroupClientFactory` (builds SDK HTTP transport from Joomla-compatible client/factories).
  - `LapayGroupClientOptions` (maps Joomla credentials/config into SDK options).
  - `LapayGroupShippingClient` (methods currently used by plugin/runtime, no old public facade duplication).
  - `LapayGroupTransportProbe` (small internal smoke harness for transport instantiation and load checks).
- Existing public facade classes (`Otpravkapochtaru`, plugin handlers, linked-select services) should migrate to these adapters directly.

## 5) Installer / updater responsibilities
- `script.php` should:
  - validate SDK payload presence after install/update.
  - ensure legacy `"Webtolk/Pochtaru"` cleanup remains (current behavior).
  - optionally log/emit a warning if runtime SDK files are missing (hard fail only if runtime immediately needs them).
  - keep plugin auto-enable behavior.
- Update post-update path to clear stale copied SDK directories under `lib_webtolk_otpravkapochtaru/src/libraries/` before install of the new package, to avoid mixed-version runtime collisions.
- Installer should not attempt manual `ClassLoader` edits in core Joomla `libraries/vendor`.

## 6) Rollback risks
- Lockfile drift can produce runtime-breaking transitive namespace changes.
- Missing transitive dependencies in the copied payload can produce class-load failures after install.
- Version mismatch between `composer.lock` and manually overridden values can desync package metadata and shipped binary.
- Mitigation: keep rollback atomic by using release artifact integrity checks and explicit rollback to prior extension zip when autoload smoke checks fail.

## 7) Files likely touched by implementation writer
- `composer.json` (add `lapaygroup/russianpost` requirement).
- `.github/workflows/release.yml` (Composer refresh + release orchestration).
- `build/release.php` (read lockfile, copy SDK, generate local autoload + manifest metadata injection).
- `.dist/build/package.config.json` (version/date replacement wiring).
- `lib_webtolk_otpravkapochtaru/src/**` (thin bridge classes + loader call).
- `script.php` (install/update validation and stale SDK cleanup).
- `lib_webtolk_otpravkapochtaru/otpravkapochtaru.xml` and `pkg_lib_wt_otpravkapochtaru.xml` (version/date metadata updates from generated lock/manual override values).
