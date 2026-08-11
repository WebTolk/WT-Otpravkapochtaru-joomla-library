# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t34-build-ci-writer`
- run_id: `lapaygroup-thin-wrapper-implementation-20260811`
- mode: writer
- workspace_access_file: `.pf/runtime/agent-runs/lapaygroup-thin-wrapper-implementation-20260811/t34-build-ci-writer/workspace-access.json`

## One Job

Implement the build/CI side of the WT Max-style architecture for this package.

## Required Tooling

- Use PHPStorm MCP first for repository/file inspection where possible.
- Use PHPStorm MCP inspections on edited PHP files after editing.
- Use shell only for Composer/build/Git checks.

## Reference Inputs

- WT Max reference:
  - `.pf/tmp/WT-Max-Joomla-library/composer.json`
  - `.pf/tmp/WT-Max-Joomla-library/.github/workflows/release.yml`
  - `.pf/tmp/WT-Max-Joomla-library/build/release.php`
- Local upstream SDK sample:
  - `.pf/tmp/LapayGroup-RussianPost-2.0.0/RussianPost-2.0.0/composer.json`
- Current project:
  - `composer.json`
  - `.gitignore`
  - package/library/plugin folders

## File Ownership

You may edit only:

- `composer.json`
- `.gitignore`
- `.github/**`
- `build/**`
- `.pf/artifacts/worker-lapaygroup-thin-wrapper-build-ci-20260811.md`

Do not edit `lib_webtolk_otpravkapochtaru/src/**`, manifests, README, language, tests, or plugin code.

## Required Changes

- Add Composer dependency on upstream `lapaygroup/russianpost` suitable for release 2.x.
- Keep PHP `>=8.3.0`, `ext-mbstring`, `ext-simplexml`, and `ext-soap`.
- Set Composer config similar to WT Max:
  - vendor dir under `build/.tmp/composer-vendor`;
  - optimized autoload;
  - GitHub HTTPS protocol;
  - `prepend-autoloader: false`.
- Add `build/release.php` adapted from WT Max for this package:
  - upstream package: `lapaygroup/russianpost`;
  - default vendor source: `build/.tmp/composer-vendor`;
  - default vendor target: `lib_webtolk_otpravkapochtaru/src/libraries/vendor`;
  - copy upstream SDK `lapaygroup/russianpost/src` into the target vendor tree;
  - generate a minimal PSR-4 autoload file for `LapayGroup\\RussianPost\\`;
  - package current Joomla package folders into `dist/*.zip`;
  - apply `__DEPLOY_VERSION__` and `__DEPLOY_DATE__` tokens.
- Add `.github/workflows/release.yml` adapted from WT Max:
  - setup PHP 8.3;
  - install extensions `zip`, `mbstring`, `soap`;
  - configure Composer to use `https://github.com/lapaygroup/RussianPost.git` as the upstream VCS repository;
  - run Composer update;
  - run `php build/release.php package-from-lock --package=lapaygroup/russianpost --env-file="${GITHUB_ENV}"`;
  - publish `dist/*.zip`.
- Ensure `.gitignore` excludes generated build/vendor/dist outputs.

## Verification

- `php -l build/release.php`
- `Get-Content -Raw composer.json | ConvertFrom-Json`
- PHPStorm MCP inspection for `build/release.php`
- Do not run broad project formatting.

## Output

Write `.pf/artifacts/worker-lapaygroup-thin-wrapper-build-ci-20260811.md`.
Include verdict, files changed, commands run, and residual risks.
