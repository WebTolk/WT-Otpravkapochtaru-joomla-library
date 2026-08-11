# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t35b-runtime-fork-dependency-cleanup`
- run_id: `lapaygroup-thin-wrapper-implementation-20260811`
- mode: corrective writer after T35
- workspace_access_file: `.pf/runtime/agent-runs/lapaygroup-thin-wrapper-implementation-20260811/t35b-runtime-fork-dependency-cleanup/workspace-access.json`

## One Job

Remove runtime facade dependencies on the old fork classes so T36 can delete the forked SDK directories.

## Required Tooling

- Use PHPStorm MCP first for repository navigation and file inspections.
- Use PHPStorm MCP inspections on edited PHP files after editing.
- Use shell only for narrow searches and PHP syntax checks.

## Current Problem To Fix

T35 changed `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`, but it still imports:

- `Webtolk\\Otpravkapochtaru\\Configuration\\CredentialsProvider`
- `Webtolk\\Otpravkapochtaru\\Entity\\*`
- `Webtolk\\Otpravkapochtaru\\Exception\\*`

That blocks the planned deletion of forked SDK files and is not a true thin wrapper.

## File Ownership

You may edit only:

- `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`
- new files under `lib_webtolk_otpravkapochtaru/src/Joomla/**`
- new files under `lib_webtolk_otpravkapochtaru/src/Transport/**`
- `.pf/artifacts/worker-lapaygroup-thin-wrapper-runtime-cleanup-20260811.md`

Do not physically delete old fork directories or files. Do not edit Composer/build/manifests/plugin/fields/services/docs/tests.

## Required Changes

- Remove all imports and type dependencies on product fork namespaces:
  - `Webtolk\\Otpravkapochtaru\\Configuration\\*`
  - `Webtolk\\Otpravkapochtaru\\Entity\\*`
  - `Webtolk\\Otpravkapochtaru\\Exception\\*`
  - `Webtolk\\Otpravkapochtaru\\Dictionaries\\*`
  - `Webtolk\\Otpravkapochtaru\\Request`
  - `Webtolk\\Otpravkapochtaru\\SoapRequest`
  - `Webtolk\\Otpravkapochtaru\\TrackingEntity`
- Preserve system plugin parameter compatibility by reading existing plugin params directly or via a new Joomla helper under owned namespace.
- Use upstream LapayGroup entity classes or arrays in public method signatures. Backward compatibility with unpublished 3.0 wrapper entity classes is not required.
- Use standard PHP exceptions or upstream exceptions; do not depend on old product exception classes.
- Keep REST-only construction working when SOAP is missing.
- Keep Joomla Form fields/services/assets out of scope.

## Verification

- PHPStorm MCP inspections for all edited PHP files.
- `php -l` for all edited PHP files.
- `Select-String` or equivalent proof that `Otpravkapochtaru.php` no longer references the old fork namespaces listed above.

## Output

Write `.pf/artifacts/worker-lapaygroup-thin-wrapper-runtime-cleanup-20260811.md`.
Include verdict, files changed, proof that fork namespace references were removed, commands run, and residual risks.
