# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t36b-fields-plugin-import-fix`
- run_id: `lapaygroup-thin-wrapper-implementation-20260811`
- mode: corrective writer after T36 orchestrator review
- workspace_access_file: `.pf/runtime/agent-runs/lapaygroup-thin-wrapper-implementation-20260811/t36b-fields-plugin-import-fix/workspace-access.json`

## One Job

Fix stale references in Joomla fields and the system plugin after T36 deleted old fork namespaces.

## Required Tooling

- Use PHPStorm MCP first for repository/file inspection where possible.
- Use named PHPStorm MCP tools if available: `read_file`, `lint_files`, `get_file_problems`.
- Use shell only for PHP syntax and focused text searches.

## Current Known Issue

After T36 removed `src/Configuration` and `src/Exception`, these files still import deleted classes:

- `lib_webtolk_otpravkapochtaru/src/Fields/AccountinfoField.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/OpslistField.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/MailtypesField.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/MailcategoriesField.php`
- `plg_system_wt_otpravkapochtaru/src/Extension/WtOtpravkapochtaru.php`

## File Ownership

You may edit only:

- `lib_webtolk_otpravkapochtaru/src/Fields/AccountinfoField.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/OpslistField.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/MailtypesField.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/MailcategoriesField.php`
- `plg_system_wt_otpravkapochtaru/src/Extension/WtOtpravkapochtaru.php`
- `.pf/artifacts/worker-lapaygroup-thin-wrapper-fields-plugin-import-fix-20260811.md`

Do not edit Composer/build/CI/manifests/installer/docs/tests or runtime facade.

## Required Changes

- Replace `Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider` with `Webtolk\Otpravkapochtaru\Joomla\CredentialsProvider`.
- Remove imports for deleted exception classes:
  - `Webtolk\Otpravkapochtaru\Exception\ConfigurationException`
  - `Webtolk\Otpravkapochtaru\Exception\TransportException`
- Preserve current user-facing messages and plugin parameter names.
- Replace catches of deleted exception classes with standard exceptions already thrown by the new Joomla wrapper:
  - missing configuration should still show existing configuration-missing messages;
  - API/client errors should still show existing API-error/unavailable messages;
  - do not introduce old fork fallback classes.
- Keep `isUnauthorized()` behavior in `AccountinfoField`, but type it against `\Throwable` or another available standard/upstream exception type.

## Verification

- PHPStorm MCP inspect edited PHP files.
- Run `php -l` with absolute paths for edited PHP files.
- Run focused search proving no remaining references to:
  - `Webtolk\Otpravkapochtaru\Configuration`
  - `Webtolk\Otpravkapochtaru\Exception`

## Output

Write `.pf/artifacts/worker-lapaygroup-thin-wrapper-fields-plugin-import-fix-20260811.md`.
Include verdict, files changed, commands run, PHPStorm inspection summary, and residual risks.
