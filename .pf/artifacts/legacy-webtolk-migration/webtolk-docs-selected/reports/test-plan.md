# Test Plan

## Objectives
- Validate implementation quality and release readiness for the Joomla Russian Post library package in a stable state.
- Confirm no new high-risk behavior is introduced before release handoff.

## Test Scope
- Static quality checks for code in `lib_webtolk_otpravkapochtaru` and plugin package.
- Packaging and release build pipeline.
- Install/enable path and runtime bootstrap checks in Joomla.local test site.
- SOAP/REST runtime behavior verification is deferred until Joomla instance is reachable.

## Environments
- Local QA environment: `D:/OSPanel/home/joomla.local/public` (blocked by DB hostname resolution currently).
- Build environment: local PHP/Phing runtime configured in `functions.shell_command` operations.

## Checks To Run
1. `php-cs-fixer` dry-run for style conformance.
2. `phpstan` in static analysis mode for typed and structural defects.
3. `phpunit` smoke run when/if test suite is available.
4. `phing` package release target verification.
5. Joomla CLI installation/validation commands.

## Browser Or Runtime Checks
- Browser runtime checks are not applicable for this implementation slice because no admin UI changes were introduced in this pass.
- Runtime install/CLI bootstrap checks are required and remain blocked by DB connectivity.

## Exit Gate
- Stage can progress to release only when:
  - `phing` release succeeds,
  - installation + migration checks on Joomla.local execute without environment errors,
  - functional smoke calls complete in reachable runtime.

## Toolchain Contract References
- Static analysis: `phpstan` via configured toolchain.
- Unit tests: `phpunit` via configured toolchain.
- Style checks: `php-cs-fixer` via configured toolchain.
- Packaging or build delivery: `phing` via configured toolchain.

## Logical Tools Used
- `php-cs-fixer`
- `phpstan`
- `phpunit`
- `phing`

## Fallback Used
- `shell` fallback for local command execution and log extraction.

## Fallback Reason
- Environment checks and filesystem/project-command verification require shell-level operations outside MCP-first policy.

## 2026-07-09 QA Tooling Plan

### Objectives
- Provide repeatable project-local entrypoints for syntax checks, code style, static analysis and PHPUnit tests.
- Use global binaries from `D:/.agents/tools/php-qa` instead of vendoring QA tools into the project.
- Keep temporary caches under `.webtolk/tmp/`.

### Checks
1. `php -l script.php; powershell -NoProfile -ExecutionPolicy Bypass -File tools/qa/lint-php.ps1`
2. `php D:/.agents/tools/php-qa/vendor/bin/phpunit --configuration=phpunit.xml`
3. `php D:/.agents/tools/php-qa/vendor/bin/phpstan analyse --configuration=phpstan.neon`
4. `php D:/.agents/tools/php-qa/vendor/bin/php-cs-fixer fix --dry-run --diff --config=.php-cs-fixer.dist.php`
5. `php D:/.agents/tools/php-qa/vendor/bin/phpcs --standard=phpcs.xml`

### Exit Gate
- Syntax, unit tests and PHPStan must pass before delivery.
- Style gates are configured as real product-source checks; current failures should be treated as formatting debt to fix or baseline explicitly in a later style-cleanup task.

## 2026-07-09 QA Tool Application Plan

### Objectives
- Apply each configured QA tool as a separate task.
- Convert the full style and coding-standard gates from runnable-but-failing to green.
- Preserve evidence in `.webtolk` reports and logs.

### Checks
1. Run PHP syntax lint.
2. Run PHPUnit.
3. Run PHPStan.
4. Apply PHP CS Fixer.
5. Re-run PHP CS Fixer in dry-run mode.
6. Run PHPCS.
7. Apply PHPCBF if PHPCS reports auto-fixable violations.
8. Re-run PHPCS.

### Exit Gate
- PHP lint must pass.
- PHPUnit must pass.
- PHPStan must pass.
- PHP CS Fixer dry-run must pass after applying fixes.
- PHPCS must pass after applying PHPCBF fixes.

### Final Status
- All direct QA gates passed after tool application.

## 2026-07-09 Documentation Rebuild Plan

### Objectives
- Move previous root `docs/` into `.webtolk` without deleting historical materials.
- Create a clean public `docs/` root for current Russian documentation.
- Cover all public methods of the library API and the Joomla administrator workflow.

### Checks
1. Verify old root docs are present under `.webtolk/docs/root-docs-archive-20260709/`.
2. Verify new root `docs/` contains only public documentation files.
3. Verify developer documentation includes the public facade methods, public entity helpers, credential provider, low-level request classes and exceptions.
4. Verify Joomla user documentation covers installation, plugin fields, account-status check, tracking credentials and troubleshooting.

### Exit Gate
- New documentation files must exist in root `docs/`.
- Flow artifacts must remain available under `.webtolk/docs/reports/`.
- No code runtime behavior is changed by the documentation cycle.
# 2026-07-11 Audit Verification Plan

## Scope
- Read-only compatibility, security, architecture, and performance audit for Joomla 5+.

## Checks
- Map Joomla symbols with Serena.
- Compare every Joomla API family used by the package against Joomla 5.4.5 and 6.1.0 core.
- Consult local Joomla documentation for deprecated HTTP and FormField behavior.
- Search for dangerous primitives, raw input, TLS bypasses, hard-coded credentials, and unescaped administrator output.
- Inspect network client lifecycle, timeouts, buffering, batching, and form-render I/O.
- Execute lint, PHPUnit, PHPStan, and PHPCS without modifying code.

## Boundary
- No product changes, live penetration test, or new runtime API calls.

## 2026-07-11 Findings 1 And 2 Verification

- Confirm framework `HttpFactory::getHttp()` exists in Joomla 5.4.5 and 6.1.0.
- Confirm deprecated CMS factory import is absent.
- Exercise safe filename normalization with traversal, separator, forbidden-character, reserved-name, NUL, empty, and Unicode cases.
- Run PhpStorm file problems, PHP lint, PHPUnit, PHPStan, PHPCS, and PHP CS Fixer dry-run.
- Rebuild ZIP and compare archived `Request.php` with source.
- Confirm only `Request.php` remains changed in tracked product files.

## Broader Coverage Proposal

- See `.webtolk/docs/reports/test-coverage-proposal-20260711.md`.

## 2026-07-11 Real REST Shipping Plan

- Hard-cap one live run at 40 calls; never call `getApiLimit()`.
- Exclude tracking.
- Exercise normalization, account/settings, shipping points, reliability, tariff, post-office lookup/services/locality, order create/edit/find, batch, documents, returns, return-to-new and delete.
- Use only entities created in this run; never fall back to an older account batch.
- Capture raw responses locally, anonymize to repository examples, infer observational schemas, validate examples, scan for leaks and verify ZIP exclusion.
- Always clean created return/order entities in `finally` where an identifier exists.

## 2026-07-11 Technical Documentation Test Plan

- Extract public methods from PHP source instead of maintaining a manual expected list.
- Require every facade method in the map and a detailed thematic chapter.
- Require «Что делает», «Зачем нужен», «Как работает» and a PHP example for each facade method.
- Verify all public methods of credentials, REST/SOAP, tracking, dictionary and entity classes are present.
- Resolve relative Markdown links and explicit anchors.
- Check fenced blocks and run `php -l` for every complete PHP snippet.
- Revalidate generated response examples after anonymization changes.
- Confirm documentation stays outside the Joomla package.
- Parse every Markdown table row by unescaped separators and require a stable column count within each table.
- Reject unescaped `|` characters inside inline-code spans located in table rows.
