# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t36-fork-prune-manifest-writer`
- run_id: `lapaygroup-thin-wrapper-implementation-20260811`
- mode: writer after T34 and T35
- workspace_access_file: `.pf/runtime/agent-runs/lapaygroup-thin-wrapper-implementation-20260811/t36-fork-prune-manifest-writer/workspace-access.json`

## One Job

Remove forked SDK surface from the product library and update Joomla manifests/installers so the WT Max-style package installs the thin wrapper and packaged upstream SDK.

## Required Tooling

- Use PHPStorm MCP first for repository/file inspection where possible.
- Use PHPStorm MCP inspections on edited PHP files after editing.
- Use shell only for file listing, package, and PHP syntax checks.

## Dependencies

Run only after T34B and T35D are complete and their artifacts exist.

## File Ownership

You may edit only:

- `lib_webtolk_otpravkapochtaru/otpravkapochtaru.xml`
- `pkg_lib_wt_otpravkapochtaru.xml`
- `script.php`
- `plg_system_wt_otpravkapochtaru/wtotpravkapochtaru.xml`
- forked SDK files/directories under `lib_webtolk_otpravkapochtaru/src/**`, except:
  - keep `Fields/**`
  - keep `Service/**`
  - keep `Otpravkapochtaru.php`
  - keep new `Joomla/**`, `libraries/**`
- `.pf/artifacts/worker-lapaygroup-thin-wrapper-prune-manifests-20260811.md`

Do not edit Composer/build/CI/docs/tests.

## Required Changes

- Delete product-owned forked SDK classes that are replaced by upstream `lapaygroup/russianpost`.
- Delete any empty/unused runtime duplicate directories left after T35C, including `src/Transport` if it has no references.
- Keep Joomla fields/services/assets.
- Ensure library manifest includes wrapper source, media assets, and packaged upstream vendor path.
- Tokenize manifest version/date with `__DEPLOY_VERSION__` and `__DEPLOY_DATE__` if build script expects that.
- Preserve system plugin settings compatibility; do not rename plugin element or existing parameter names.
- Preserve existing installer behavior:
  - legacy pre-3.0 library removal;
  - plugin enablement;
  - PHP/Joomla checks;
  - non-blocking SOAP post-install/post-update warning.

## Verification

- PHPStorm MCP inspections for edited XML/PHP where available.
- `php -l script.php`
- Archive dry-run with the project build path if available.

## Output

Write `.pf/artifacts/worker-lapaygroup-thin-wrapper-prune-manifests-20260811.md`.
Include verdict, files changed/deleted, compatibility notes, commands run, and residual risks.
