# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t37-docs-tests-writer`
- run_id: `lapaygroup-thin-wrapper-implementation-20260811`
- mode: writer after T34-T36
- workspace_access_file: `.pf/runtime/agent-runs/lapaygroup-thin-wrapper-implementation-20260811/t37-docs-tests-writer/workspace-access.json`

## One Job

Update project documentation and focused tests/checks for the WT Max-style thin wrapper architecture.

## Required Tooling

- Use PHPStorm MCP first for repository/file inspection where possible.
- Use PHPStorm MCP inspections on edited PHP files after editing.
- Use shell only for tests/build commands.

## Dependencies

Run only after T34B, T35D, T36, and T36B complete and their artifacts exist.

## File Ownership

You may edit only:

- `README.md`
- `docs/**`
- `tests/**`
- `phpunit.xml`
- `.pf/artifacts/worker-lapaygroup-thin-wrapper-docs-tests-20260811.md`

Do not edit Composer/build/CI/manifests/runtime/plugin/field source.

## Required Changes

- Document that the package is a Joomla wrapper around upstream `lapaygroup/russianpost`.
- Document GitHub/Composer build pulling upstream SDK.
- Document ready Joomla ZIP behavior and SOAP warning behavior.
- Document that Joomla Form fields and their web assets are library-owned.
- Add focused regression tests or scripts where practical:
  - composer requires `lapaygroup/russianpost` and `ext-soap`;
  - installer required extensions do not include SOAP;
  - package contains upstream vendor autoload after build.
  - no runtime or field code references deleted fork namespaces.
- Keep docs honest about current test coverage and residual risks.

## Verification

- PHPStorm MCP inspections for edited PHP test files where applicable.
- Run focused tests/scripts added or existing related QA checks.

## Output

Write `.pf/artifacts/worker-lapaygroup-thin-wrapper-docs-tests-20260811.md`.
Include verdict, files changed, commands run, and residual risks.
