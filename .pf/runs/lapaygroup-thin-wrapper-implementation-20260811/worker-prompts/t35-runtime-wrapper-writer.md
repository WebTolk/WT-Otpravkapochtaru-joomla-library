# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t35-runtime-wrapper-writer`
- run_id: `lapaygroup-thin-wrapper-implementation-20260811`
- mode: writer
- workspace_access_file: `.pf/runtime/agent-runs/lapaygroup-thin-wrapper-implementation-20260811/t35-runtime-wrapper-writer/workspace-access.json`

## One Job

Implement the Joomla runtime wrapper layer around upstream `LapayGroup\\RussianPost`.

## Required Tooling

- Use PHPStorm MCP first for repository navigation and file inspections.
- Use PHPStorm MCP inspections on edited PHP files after editing.
- Use shell only for narrow searches and PHP syntax checks.

## Reference Inputs

- WT Max wrapper:
  - `.pf/tmp/WT-Max-Joomla-library/lib_webtolk_wtmax/src/Wtmax.php`
- Local upstream SDK sample:
  - `.pf/tmp/LapayGroup-RussianPost-2.0.0/RussianPost-2.0.0/src/**`
  - `.pf/tmp/LapayGroup-RussianPost-2.0.0/RussianPost-2.0.0/composer.json`
- Current wrapper/plugin settings:
  - `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`
  - `plg_system_wt_otpravkapochtaru/**`
  - `.pf/artifacts/worker-lapaygroup-joomla-psr-transport-prototype-20260811.md` if useful

## File Ownership

You may edit only:

- `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`
- new files under `lib_webtolk_otpravkapochtaru/src/Joomla/**`
- new files under `lib_webtolk_otpravkapochtaru/src/Transport/**`
- new files under `lib_webtolk_otpravkapochtaru/src/libraries/**` only when creating a placeholder `.gitkeep` if needed
- `.pf/artifacts/worker-lapaygroup-thin-wrapper-runtime-20260811.md`

Do not delete forked SDK files in this task. Do not edit Composer/build/manifests/plugin/fields/tests/docs.

## Required Changes

- Turn `Webtolk\\Otpravkapochtaru\\Otpravkapochtaru` into a thin Joomla facade for upstream SDK.
- Load the packaged upstream autoloader from `src/libraries/vendor/autoload.php`.
- Preserve system plugin parameter compatibility:
  - existing plugin parameter names must remain readable;
  - do not require users to re-enter existing REST/SOAP credentials.
- Use Joomla HTTP as the client layer and Laminas factories from Joomla for PSR factories, following Joomla way.
- If a Joomla-to-PSR18 adapter is needed, implement it under an owned namespace such as `Webtolk\\Otpravkapochtaru\\Transport`.
- Keep tracking SOAP optional behavior clear; do not make constructor/factory fail for REST-only usage when SOAP is absent.
- Keep Joomla Form fields and field services out of scope.

## Verification

- PHPStorm MCP inspections for edited PHP files.
- `php -l` for every edited PHP file.
- A narrow smoke script may be created under `.pf/tmp` if needed, but do not edit product files outside ownership.

## Output

Write `.pf/artifacts/worker-lapaygroup-thin-wrapper-runtime-20260811.md`.
Include verdict, files changed, API assumptions, commands run, and residual risks.
