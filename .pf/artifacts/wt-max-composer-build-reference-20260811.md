# WT Max Composer Build Reference

## Scope

Reference repository:

- `https://github.com/WebTolk/WT-Max-Joomla-library`

This artifact captures the build pattern that should be reused for
`WT-Otpravkapochtaru-joomla-library` before launching thin-wrapper planning
workers.

## Observed WT Max Pattern

- `composer.json` declares the upstream SDK as a Composer dependency:
  - `webtolk/max: *`
  - Composer runtime vendor directory is redirected to `build/.tmp/composer-vendor`.
- GitHub Actions release workflow:
  - runs on manual `workflow_dispatch` and pushed `v*` tags;
  - installs PHP and Composer;
  - runs `composer update --with-dependencies`;
  - calls `php build/release.php package-from-lock`;
  - publishes `dist/*.zip` as a GitHub release asset.
- `build/release.php`:
  - reads upstream SDK version/date from `composer.lock`;
  - allows manual Joomla package version override;
  - copies only the upstream SDK runtime `src` tree into the Joomla library;
  - generates a small package-local SDK autoloader;
  - stages Joomla package files and creates the final ZIP.

## Target Adaptation For This Project

Use the same release architecture, adapted to Russian Post:

- add Composer dependency on `lapaygroup/russianpost`;
- keep Composer install/update output outside the committed Joomla runtime tree,
  for example under `build/.tmp/composer-vendor`;
- add a GitHub Actions release workflow that updates the SDK from Composer on
  each release build;
- add/adapt a release script that copies the required LapayGroup runtime source
  into `lib_webtolk_otpravkapochtaru/src/libraries/vendor/lapaygroup/russianpost`
  or another clearly package-local runtime vendor directory;
- generate package-local autoload for `LapayGroup\RussianPost\`;
- derive default package version/date from `composer.lock`, with optional manual
  Joomla package version override;
- keep the Joomla wrapper limited to settings storage, settings-to-SDK config,
  Joomla Form fields, WebAssets, Registry conveniences and Joomla-way transport
  wiring.

## Explicit Boundaries

- Do not put JoomShopping integration into the library.
- Do not preserve old unreleased 3.0.0 public facade compatibility.
- Preserve compatibility only for existing system plugin parameters.
- Product code is not changed by this artifact.
