# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t35c-runtime-contract-fix`
- run_id: `lapaygroup-thin-wrapper-implementation-20260811`
- mode: corrective writer after T35B review
- workspace_access_file: `.pf/runtime/agent-runs/lapaygroup-thin-wrapper-implementation-20260811/t35c-runtime-contract-fix/workspace-access.json`

## One Job

Make the runtime wrapper thin and internally consistent before old fork classes are deleted.

## Required Tooling

- Use PHPStorm MCP first for repository/file inspection where possible.
- Use named PHPStorm MCP tools if available, especially `read_file`, `lint_files`, `get_file_problems`, or structural search.
- Use shell only for PHP syntax and focused text searches.

## Current Known Issues

- `lib_webtolk_otpravkapochtaru/src/Joomla/UploadedFileSerializer.php` and `lib_webtolk_otpravkapochtaru/src/Transport/UploadedFileSerializer.php` duplicate the same class responsibility.
- `Otpravkapochtaru.php` normalizes payload to anonymous/object values where upstream methods expect `LapayGroup\RussianPost\Entity\Order`, `Recipient`, and `ReturnShipment`.
- The facade must not depend on old fork namespaces such as `Webtolk\Otpravkapochtaru\Entity`, `Configuration`, `Exception`, `Request`, `SoapRequest`, `Dictionaries`, or `TrackingEntity`.

## File Ownership

You may edit only:

- `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`
- `lib_webtolk_otpravkapochtaru/src/Joomla/CredentialsProvider.php`
- `lib_webtolk_otpravkapochtaru/src/Joomla/Psr18TransportFactory.php`
- `lib_webtolk_otpravkapochtaru/src/Joomla/UploadedFileSerializer.php`
- `lib_webtolk_otpravkapochtaru/src/Transport/UploadedFileSerializer.php`
- `.pf/artifacts/worker-lapaygroup-thin-wrapper-runtime-contract-fix-20260811.md`

Do not edit Composer/build/CI/manifests/plugin/assets/docs/tests.

## Required Changes

- Keep only one uploaded file serializer. Prefer the `Joomla` namespace if it is what `Otpravkapochtaru.php` uses.
- Remove the unused duplicate serializer file if it is not referenced.
- Replace anonymous payload adapters with upstream LapayGroup entity construction when the upstream method signature requires a concrete entity:
  - `LapayGroup\RussianPost\Entity\Order`;
  - `LapayGroup\RussianPost\Entity\Recipient`;
  - `LapayGroup\RussianPost\Entity\ReturnShipment`.
- Preserve the current public facade method names/signatures as much as possible, but do not keep compatibility with old fork entity classes.
- Preserve system plugin settings parameter compatibility in `CredentialsProvider`.
- Do not add fallback support for old fork classes.
- Do not vendor upstream SDK into `src/libraries/vendor`; build script owns that.

## Verification

- Use PHPStorm MCP inspections on every edited PHP file.
- Run `php -l` for every edited PHP file.
- Run focused search proving old fork namespaces are not referenced by the kept runtime facade/Joomla helper files.
- Run focused search proving deleted duplicate serializer is not referenced.

## Output

Write `.pf/artifacts/worker-lapaygroup-thin-wrapper-runtime-contract-fix-20260811.md`.
Include verdict, files changed/deleted, commands run, PHPStorm inspection summary, and residual risks.
