# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t37b-docs-tests-fix`
- run_id: `lapaygroup-thin-wrapper-implementation-20260811`
- mode: corrective writer after failed/partial T37
- workspace_access_file: `.pf/runtime/agent-runs/lapaygroup-thin-wrapper-implementation-20260811/t37b-docs-tests-fix/workspace-access.json`

## One Job

Finish and correct the docs/tests changes left incomplete by T37 so they match the thin-wrapper architecture and no longer document removed fork APIs.

## Required Tooling

- Use PHPStorm MCP first for reading/checking files where possible.
- Use named PHPStorm MCP tools if available: `read_file`, `lint_files`, `get_file_problems`.
- Use shell only for focused tests, PHP syntax, and text searches.

## Current Known Issues From Orchestrator Review

- `README.md` still contains old examples importing:
  - `Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider`
  - `Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException`
  - `Webtolk\Otpravkapochtaru\Request`
  - `Webtolk\Otpravkapochtaru\Entity\Order`
- `README.md` says SDK source is copied to `src/libraries/lapaygroup/...`; actual build target includes `src/libraries/vendor/lapaygroup/...`.
- `docs/README.md` still describes deleted low-level classes (`Request`, `SoapRequest`, `TrackingEntity`, `CountryDictionary`) as current interface.
- `tests/Unit/Architecture/ThinWrapperContractTest.php` currently:
  - likely calculates project root incorrectly from `tests/Unit/Architecture`;
  - searches for deleted `Webtolk\Pochtaru` instead of deleted fork namespaces in this project;
  - should also guard against old low-level class references in current runtime/docs where practical.

## File Ownership

You may edit only:

- `README.md`
- `docs/README.md`
- `docs/thin-wrapper-architecture.md`
- `tests/**`
- `phpunit.xml`
- `.pf/artifacts/worker-lapaygroup-thin-wrapper-docs-tests-fix-20260811.md`

Do not edit Composer/build/CI/manifests/installer/runtime/plugin/field source.

## Required Changes

- Update docs to state current 3.0 architecture:
  - Joomla package wraps upstream `lapaygroup/russianpost`;
  - build stages upstream SDK under `lib_webtolk_otpravkapochtaru/src/libraries/vendor/...`;
  - public Joomla entry point is `Webtolk\Otpravkapochtaru\Otpravkapochtaru`;
  - Joomla fields/assets are library-owned.
- Remove or rewrite examples that instantiate deleted fork classes:
  - no `Configuration\CredentialsProvider`;
  - no `Request`;
  - no `Entity\Order::fromArray()`;
  - no `OtpravkapochtaruException` from deleted namespace.
- Correct tests so they actually read the project root from `tests/Unit/Architecture`.
- Tests should validate:
  - composer requires `lapaygroup/russianpost`, `ext-soap`, and `ext-zip`;
  - installer required extension list includes `mbstring` and excludes hard-fail `soap`;
  - docs/runtime source do not reference deleted namespaces/classes as active API;
  - package/archive smoke test is skipped if archive is absent, but expects `src/libraries/vendor/autoload.php` and `src/libraries/vendor/lapaygroup/russianpost/src/` when archive exists.
- Keep tests focused and deterministic. Do not require network.

## Verification

- PHPStorm MCP inspect edited PHP test files.
- Run `php -l` on edited PHP test files.
- Run focused PHPUnit command for the architecture test if local PHPUnit is available; otherwise report why not.
- Run focused `rg` checks for removed API references in README/docs/tests.

## Output

Write `.pf/artifacts/worker-lapaygroup-thin-wrapper-docs-tests-fix-20260811.md`.
Include verdict, files changed, commands run, PHPStorm inspection summary, test result, and residual risks.
