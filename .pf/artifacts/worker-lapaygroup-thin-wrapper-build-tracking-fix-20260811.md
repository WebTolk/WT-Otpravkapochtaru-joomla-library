# Worker artifact: t34b-build-tracking-fix

## Assignment
- task_id: `t34b-build-tracking-fix`
- run_id: `lapaygroup-thin-wrapper-implementation-20260811`
- model: `gpt-5.3-codex-spark`
- scope: shell-worker corrective writer after T34
- verdict: **partial pass**

## Files changed
- `.gitignore`
  - removed broad `build/` ignore so build scripts can be tracked.
  - kept `build/.tmp/` and `build/.stage/` ignored patterns.
  - kept `dist/` ignored.
- `composer.json`
  - added `ext-zip` to `require`.
  - preserved existing requirements (`php>=8.3.0`, `ext-mbstring`, `ext-simplexml`, `ext-soap`, `lapaygroup/russianpost`).
  - retained `build/.tmp/composer-vendor` via existing composer config.
- `.github/workflows/release.yml`
  - normalized workflow title and release title text to clearer product naming:
    - workflow: `Build and Release WT Otpravkapochtaru Package`
    - GitHub release name: `WT Otpravkapochtaru Package ...`
- `build/release.php`
  - removed unused `$projectRoot` parameter from `prepareSdk()` calls/signature.
  - updated `writeMetadataJson()` to catch `JsonException` and fail explicitly if JSON encoding fails.

## Commands run
- `Get-Content -Raw composer.json | ConvertFrom-Json | ConvertTo-Json -Depth 10`
  - succeeded (exit code 0); output includes `ext-zip` in `require`.
- `php -l D:\Dev\WT-Otpravkapochtaru-joomla-library\build\release.php`
  - success: `No syntax errors detected`.
- `git status --ignored --short build` (from `D:\Dev\WT-Otpravkapochtaru-joomla-library`)
  - output: `?? build/`
- `git check-ignore -v build/release.php`
  - exit code 1 (not ignored).
- `git check-ignore -v build/.tmp/; git check-ignore -v build/.stage/; git check-ignore -v dist/`
  - confirmed ignored sources:
    - `.gitignore:19:*.tmp	build/.tmp/`
    - `.gitignore:12:build/.stage/	build/.stage/`
    - `.gitignore:14:dist/	dist/`

## PHPStorm MCP
- Attempted required PHPStorm MCP inspection path; calls to `phpstorm` tools returned cancellations in this environment, so direct shell verification was used for syntax and parsing checks.

## Residual risks
- `build/` is now visible and therefore not ignored, but the directory itself is currently untracked in the workspace (`?? build/`), so it still needs explicit Git add/commit in normal delivery flow to make `build/release.php` part of the tracked product artifacts.
- `.github/` is shown as untracked in workspace status; ensure the workflow file path is intentionally included by project conventions before handoff.
