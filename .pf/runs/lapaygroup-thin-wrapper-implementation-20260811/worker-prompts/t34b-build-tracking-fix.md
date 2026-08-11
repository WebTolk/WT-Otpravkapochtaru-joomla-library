# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t34b-build-tracking-fix`
- run_id: `lapaygroup-thin-wrapper-implementation-20260811`
- mode: corrective writer after T34 review
- workspace_access_file: `.pf/runtime/agent-runs/lapaygroup-thin-wrapper-implementation-20260811/t34b-build-tracking-fix/workspace-access.json`

## One Job

Fix only the build/CI tracking issues found after T34, so WT Max-style build files are actually part of product code and Composer metadata matches the build script.

## Required Tooling

- Use PHPStorm MCP first for reading/checking files where possible.
- Use named PHPStorm MCP tools if available, especially `read_file`, `lint_files`, or `get_file_problems`.
- Use shell only for Git/Composer/PHP syntax checks.

## Current Known Issues

- `.gitignore` still ignores the whole `build/` directory, so `build/release.php` is not trackable.
- `build/release.php` uses `ZipArchive`; Composer should declare the build/runtime requirement explicitly if the package build depends on it.
- Workflow release name should use clear product naming.
- PHPStorm previously reported weak issues in `build/release.php`: unused `$projectRoot` in `prepareSdk()` and possible unhandled JSON write exceptions.

## File Ownership

You may edit only:

- `.gitignore`
- `composer.json`
- `.github/workflows/release.yml`
- `build/release.php`
- `.pf/artifacts/worker-lapaygroup-thin-wrapper-build-tracking-fix-20260811.md`

Do not edit runtime source, Joomla manifests, plugin files, tests, docs, or other `.pf` files.

## Required Changes

- Make `build/release.php` visible to Git while keeping generated build outputs ignored:
  - keep `build/.tmp/` ignored;
  - keep `build/.stage/` ignored;
  - keep `dist/` ignored;
  - do not ignore the whole `build/` directory.
- Add `ext-zip` to Composer requirements if it is missing.
- Keep existing requirements: PHP `>=8.3.0`, `ext-mbstring`, `ext-simplexml`, `ext-soap`, `lapaygroup/russianpost`.
- Keep Composer vendor dir under `build/.tmp/composer-vendor`.
- Clean the release workflow display strings if they are awkward, without changing the architecture.
- Fix simple PHPStorm warnings in `build/release.php` when they are low-risk and inside this ownership boundary.

## Verification

- Use PHPStorm MCP inspection on `build/release.php`, `composer.json`, `.github/workflows/release.yml`, and `.gitignore` where supported.
- Run `php -l build/release.php`.
- Run `Get-Content -Raw composer.json | ConvertFrom-Json`.
- Run `git status --ignored --short build` and confirm `build/release.php` is not ignored while generated subdirectories remain ignored.

## Output

Write `.pf/artifacts/worker-lapaygroup-thin-wrapper-build-tracking-fix-20260811.md`.
Include verdict, files changed, commands run, verification evidence, and residual risks.
