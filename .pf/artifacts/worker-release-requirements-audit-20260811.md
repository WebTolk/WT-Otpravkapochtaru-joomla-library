# Release Requirements Audit: t25-release-requirements-audit

run_id: release-readiness-audit-20260811
run_mode: read-only
assignment_scope: package system requirements consistency + Joomla local documentation support
verdict: pass

## Findings

- `composer.json` requires PHP `>=8.3.0` under `require.php`.
- `composer.json` requires `ext-mbstring` and `ext-simplexml`.
- `composer.json` does not declare `ext-soap`.
- `script.php` sets minimum PHP in `minimumPhp` as `8.3.0`.
- `script.php` declares required PHP extensions as `['mbstring']` and validates each with `extension_loaded(...)`.
- `script.php` emits installer error text through `PKG_LIB_WT_OTPRAVKAPOCHTARU_ERROR_REQUIRED_PHP_EXTENSION` for any missing extension.
- Language keys for required extension errors exist in both language files via `PKG_LIB_WT_OTPRAVKAPOCHTARU_ERROR_REQUIRED_PHP_EXTENSION`.
- `README.md` explicitly states SOAP is optional and only for tracking functionality while still requiring PHP 8.3.0+ and mbstring.

## Local Joomla documentation evidence

- `D:/.agents/docs/joomla/core/Joomla-core/6.x/6.1.2/installation/joomla.php` defines `JOOMLA_MINIMUM_PHP` as `8.3.0`.
- `D:/.agents/docs/joomla/core/Joomla-core/6.x/6.1.2/administrator/index.php` defines `JOOMLA_MINIMUM_PHP` as `8.3.0`.
- `D:/.agents/docs/joomla/core/Joomla-core/6.x/6.1.2/libraries/vendor/composer/platform_check.php` requires `PHP_VERSION_ID >= 80300`.
- `D:/.agents/docs/joomla/core/Joomla-core/5.x/5.4.5/installation/joomla.php` defines `JOOMLA_MINIMUM_PHP` as `8.1.0`.
- `D:/.agents/docs/joomla/core/Joomla-core/5.x/5.4.5/installation/src/Model/ChecksModel.php` explicitly includes mbstring-related checks (language/usage), matching the mbstring sensitivity in Joomla installer checks.
- No `soap` matches were found in the checked Joomla core bootstrap/requirement files (`installation/joomla.php`, `ChecksModel.php`, `platform_check.php`) for both 6.1.2 and 5.4.5.

## Residual risk

- No functional mismatch is detected in requirement declarations.
- Residual: `README.md` is informative and still describes SOAP as optional for tracking, which is consistent with package checks, but SOAP-dependent behavior is not guarded by an installer gate and may require runtime user validation in use.
