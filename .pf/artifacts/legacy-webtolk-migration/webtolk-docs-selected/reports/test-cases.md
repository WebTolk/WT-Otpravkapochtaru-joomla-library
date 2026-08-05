# Test Cases

## 2026-07-10 Docblock Clarification Cases

### Executed Cases
1. Checked PhpStorm PHP project config and Composer dependency metadata.
2. Ran PhpStorm inspections on the main changed files.
3. Checked changed PHP files with `php -l`.
4. Ran project PHP lint helper: `tools/qa/lint-php.ps1`.
5. Ran PHP CS Fixer dry-run with `.php-cs-fixer.dist.php`.
6. Ran PHPCS with `phpcs.xml`.
7. Ran PHPStan with `phpstan.neon`.
8. Ran PHPUnit with `phpunit.xml`.

### Actual Results
1. PhpStorm reports project language level `8.1` and interpreter PHP `8.3.30`; SOAP and SimpleXML are loaded.
2. PhpStorm `WARNING+` findings for missing `ext-soap` and `ext-simplexml` were fixed in `composer.json`; recheck passed on affected files.
3. Syntax passed for all changed PHP files.
4. Project lint helper passed.
5. PHP CS Fixer dry-run passed; the known PHP 8.3 vs project PHP 8.1 warning remains.
6. PHPCS passed.
7. PHPStan passed with no errors.
8. PHPUnit passed: `3 tests`, `4 assertions`.

### Residual Failures
- None for the configured gates.

### Residual Risks
- This was a documentation-only pass; no live Joomla install/runtime smoke was rerun.
- PhpStorm `WEAK_WARNING` items still include style/noise such as missing method-level `@since` tags and duplicate class definitions caused by indexed `.webtolk/tmp` and legacy build copies; no `ERROR` findings were reported in the key checked files.

## Executed Cases
1. Style verification for extension/package source files using `php-cs-fixer` dry-run.
2. Static analysis using `phpstan`.
3. Automated test run using `phpunit`.
4. Release package generation using `phing`.
5. Joomla.local install/validation attempts through CLI and bootstrap entrypoint.

## Expected Results
1. No formatting deltas in dry-run.
2. Static issues are limited to external dependency context.
3. Tests execute (or report clear suite absence).
4. Package builds successfully and creates zip.
5. CLI install succeeds and package can be installed/validated in Joomla.

## Actual Results
1. Passed after style fixes; dry-run reported no remaining changes.
2. Completed with environment-stub limitations; external CMS symbol issues reported.
3. Executed, but no tests discovered in repository context.
4. Initially failed (missing `ZipArchive`), then passed after phing PHP extension fix.
5. Blocked: Joomla bootstrap fails at DB host resolution (`getaddrinfo` for `mariadb-11.8`).

## Failures
- Runtime install/validation blocked by environment DB DNS/host issue.
- Static analysis warnings from missing Joomla stubs in this environment.

## Skipped Cases
- Full browser-assisted acceptance and runtime behavior scripts.
- Functional smoke calls against live Joomla test stack.

## Evidence Links
- `docs/reports/review-findings.md`
- `docs/reports/test-plan.md`
- `.webtolk/logs/verification-log.md`
- `.webtolk/logs/task-log.md`
- `.webtolk/logs/agent-log.md`
- `.webtolk/logs/joomla-orchestrator.md`

## Toolchain Contract References
- `php-cs-fixer` via configured toolchain.
- `phpstan` via configured toolchain.
- `phpunit` via configured toolchain.
- `phing` via configured toolchain.

## Logical Tools Used
- `php-cs-fixer`
- `phpstan`
- `phpunit`
- `phing`
- `shell`

## Fallback Used
- `shell` used for command execution and logs/verification checks.

## Fallback Reason
- Test/verification in local environment and build CLI checks require direct shell execution.

## 2026-07-09 QA Tooling Cases

### Executed Cases
1. PHP syntax lint over package entrypoint, library, plugin and tests.
2. PHPUnit 9.6 run with `phpunit.xml`.
3. PHPStan 2.1 run with Joomla 6.1 core mirror bootstrap.
4. PHP CS Fixer dry-run on newly added tests.
5. PHPCS on newly added tests.
6. Full PHP CS Fixer and PHPCS over product source to confirm style gates are runnable.

### Actual Results
1. PHP syntax lint passed.
2. PHPUnit passed: `3 tests`, `4 assertions`.
3. PHPStan passed: no errors.
4. PHP CS Fixer on `tests/` passed.
5. PHPCS on `tests/` passed.
6. Full style gates ran and reported existing formatting debt in product files.

### Historical Residual Failures Before Tool Application
- `php-cs-fixer` full dry-run reports formatting deltas in existing product code.
- `phpcs` full run reports existing formatting issues, especially `CountryDictionary.php`, `PlugininfoField.php`, and several brace/spacing issues.
- `composer --version` fails in this shell with `Could not open input file: \composer.phar`; direct global binary invocations were used instead.

## 2026-07-09 QA Tool Application Cases

### Executed Cases
1. PHP syntax lint over package entrypoint, library, plugin and tests.
2. PHPUnit 9.6 run with `phpunit.xml`.
3. PHPStan 2.1 run with `phpstan.neon`.
4. PHP CS Fixer full apply over configured paths.
5. PHP CS Fixer full dry-run after apply.
6. PHPCS full run over configured paths.
7. PHPCBF auto-fix for PHPCS-fixable violations.
8. PHPCS full run after PHPCBF.

### Actual Results
1. PHP syntax lint passed.
2. PHPUnit passed: `3 tests`, `4 assertions`.
3. PHPStan passed: no errors.
4. PHP CS Fixer applied formatting to `18` files.
5. PHP CS Fixer dry-run passed after formatting.
6. Initial PHPCS found `5` auto-fixable errors in `3` files.
7. PHPCBF fixed all `5` errors.
8. Final PHPCS passed.

### Residual Failures
- None in the configured direct QA gates after tool application.

### Tooling Constraints
- PHP CS Fixer still warns because it runs on PHP `8.3.30` while `composer.json` declares platform PHP `8.1.0`.
- Composer script aliases remain unverified in this shell because `composer --version` resolves to missing `\composer.phar`; direct global binary invocations are verified.

## 2026-07-09 Documentation Rebuild Cases

### Executed Cases
1. Checked the new root `docs/` inventory.
2. Checked the archived previous root docs path.
3. Checked `.webtolk/docs/reports/` availability after moving root docs.
4. Compared documentation scope with public methods discovered in `lib_webtolk_otpravkapochtaru/src` and plugin service provider.

### Actual Results
1. Root `docs/` contains `README.md`, `developer-api.md`, and `joomla-user-guide.md`.
2. Previous root docs are present at `.webtolk/docs/root-docs-archive-20260709/`.
3. Required flow reports are available under `.webtolk/docs/reports/`.
4. Developer documentation covers the public facade methods, entity `fromArray()` / `toArray()` methods, `CredentialsProvider`, `Request`, `SoapRequest`, `TrackingEntity`, `CountryDictionary`, exceptions and Joomla service provider.

### Residual Failures
- None for this documentation-only cycle.

## 2026-07-09 Documentation Review Cases

### Executed Cases
1. Compared public methods in `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php` with `docs/facade-method-reference.md`.
2. Compared unique public method names in `lib_webtolk_otpravkapochtaru/src` and `plg_system_wt_otpravkapochtaru/services` with `docs/developer-api.md` and `docs/facade-method-reference.md`.
3. Checked that the practical facade reference contains `Что делает`, `Зачем нужен`, code examples and data-structure blocks.
4. Counted Markdown code fences in `docs/*.md` to confirm code/data examples are present broadly.

### Actual Results
1. All `35` public facade methods are documented in the practical reference.
2. All `57` unique public library method names are represented in the documentation set.
3. The practical reference contains `104` method-purpose/data-structure markers.
4. The documentation set contains `170` Markdown code fence markers.

### Residual Failures
- None in documentation coverage checks.

## 2026-07-09 Public Repository Cases

### Executed Cases
1. Checked GitHub CLI authentication and WebTolk organization access.
2. Checked that `WebTolk/WT-Otpravkapochtaru-joomla-library` does not already exist.
3. Verified `.gitignore` excludes `.webtolk`, `.packages`, `.idea`, `.serena` and `.phpunit.result.cache`.
4. Ran PHP syntax lint.
5. Ran PHPUnit.
6. Ran PHPStan.
7. Ran PHP CS Fixer dry-run.
8. Ran PHPCS.

### Actual Results
1. GitHub CLI is authenticated as `sergeytolkachyov` with repo and organization read scopes.
2. Repository lookup returned not found before creation.
3. Internal folders and caches are ignored by git.
4. PHP syntax lint passed.
5. PHPUnit passed: `3 tests`, `4 assertions`.
6. PHPStan passed: no errors.
7. PHP CS Fixer dry-run passed with the known PHP runtime mismatch warning.
8. PHPCS passed.
9. Public GitHub repository was created: `https://github.com/WebTolk/WT-Otpravkapochtaru-joomla-library`.
10. Commit `b4b9a83` was pushed to `main`; local `main` tracks `origin/main`.
11. GitHub API confirmed remote `README.md` and `LICENSE` content URLs.

### Residual Failures
- None in direct publication checks before commit.

## 2026-07-09 Installer And Docblock Cases

### Executed Cases
1. Checked global/local WebTolk template sources and Joomla `InstallerScriptInterface` signature.
2. Ran PHP syntax lint over product files and tests.
3. Ran PHPUnit.
4. Ran PHPStan.
5. Ran PHP CS Fixer dry-run.
6. Ran PHPCS.
7. Built release package with Phing.
8. Inspected release ZIP for `script.php` and installer language files.
9. Checked product PHP file headers for standardized WebTolk docblock tags.

### Actual Results
1. No standalone global WebTolk `script.php` template file was found; current Joomla 4+ provider pattern was updated against the platform interface contract.
2. PHP syntax lint passed.
3. PHPUnit passed: `3 tests`, `4 assertions`.
4. PHPStan passed: no errors.
5. PHP CS Fixer dry-run passed with the known PHP runtime mismatch warning.
6. PHPCS passed.
7. `.packages/WT Otpravkapochtaru_3.0.0.zip` was rebuilt successfully.
8. ZIP contains root `script.php` and both package sys language files.
9. Product PHP files contain `150` standard WebTolk docblock tag lines across `25` files; old spacing variants were not found.

### Residual Failures
- None in direct installer/docblock checks.

## 2026-07-09 Installer Message Output Fix Cases

### Executed Cases
1. Searched `script.php` for direct output calls: `echo`, `print`, `var_dump`, `dump`.
2. Ran PHP syntax lint.
3. Ran PHPUnit.
4. Ran PHPStan.
5. Ran PHP CS Fixer dry-run.
6. Ran PHPCS.
7. Rebuilt the release package.
8. Inspected archived `script.php` inside `.packages/WT Otpravkapochtaru_3.0.0.zip`.

### Actual Results
1. No direct output calls were found in `script.php`.
2. PHP syntax lint passed.
3. PHPUnit passed: `3 tests`, `4 assertions`.
4. PHPStan passed: no errors.
5. PHP CS Fixer dry-run passed with the known PHP runtime mismatch warning.
6. PHPCS passed.
7. Release package build passed.
8. Archived `script.php` has `echo_count=0` and `enqueue_count=1`.

### Residual Failures
- None.

## 2026-07-09 Branded Installer Script Adaptation

### Executed Cases
1. Checked the global WebTolk branding source under `D:\.agents`.
2. Searched `script.php` for direct output calls: `echo`, `print`, `var_dump`, `dump`.
3. Ran PHP syntax lint.
4. Ran product/test PHP syntax lint.
5. Ran PHPUnit.
6. Ran PHPStan.
7. Ran PHPCS.
8. Ran PHP CS Fixer dry-run.
9. Rebuilt the release package with Phing.
10. Inspected archived `script.php` inside `.packages/WT Otpravkapochtaru_3.0.0.zip`.

### Actual Results
1. The branding source was applied as a WebTolk post-installation message pattern for this package.
2. No direct output calls were found in `script.php`.
3. `php -l script.php` passed.
4. `tools/qa/lint-php.ps1` passed.
5. PHPUnit passed: `3 tests`, `4 assertions`.
6. PHPStan passed: no errors.
7. PHPCS passed.
8. PHP CS Fixer dry-run passed with no changed files and the known PHP runtime mismatch warning.
9. Release package build passed.
10. Archived `script.php` has `EchoCount = 0`, `PrintCount = 0`, `EnqueueHtmlInfo = True`, `HasBrandMessage = True`, `HasWebtolkLogo = True`, `HasGithubLink = True`.

### Residual Failures
- None in static QA and archive verification.

## 2026-07-10 Method-Level Since Tag Cases

### Executed Cases
1. Re-ran a targeted method-docblock scanner over `script.php`, `lib_webtolk_otpravkapochtaru/**/*.php`, and `plg_system_wt_otpravkapochtaru/**/*.php`.
2. Verified the scanner handles `public static function` methods as well as regular public/protected/private methods.
3. Ran PhpStorm inspections on representative changed files: `AddressReturn.php`, `Otpravkapochtaru.php`, and `script.php`.
4. Ran product/test PHP syntax lint.
5. Ran PHP CS Fixer dry-run.
6. Ran PHPCS.
7. Ran PHPStan.
8. Ran PHPUnit.
9. Ran `git diff --check`.

### Actual Results
1. The method-docblock scanner passed: all methods with PHPDoc include method-level `@since`.
2. `AddressReturn::fromArray()` and the other static factory docblocks now include `@since 3.0.0`.
3. PhpStorm reported no missing `@since`; `AddressReturn.php` and `script.php` were clean at `WEAK_WARNING`, while `Otpravkapochtaru.php` only retained unrelated weak warnings from duplicate definitions and duplicate-code inspection.
4. Product/test PHP syntax lint passed.
5. PHP CS Fixer dry-run passed with no changed files and the known PHP runtime mismatch warning.
6. PHPCS passed.
7. PHPStan passed with no errors.
8. PHPUnit passed: `3 tests`, `4 assertions`.
9. `git diff --check` passed.

### Residual Failures
- None for method-level `@since` coverage or CLI QA.

## 2026-07-10 Package Rebuild And Push Cases

### Executed Cases
1. Ran `phing -f D:\Dev\WT-Otpravkapochtaru-joomla-library\phing.xml "3. Package release"`.
2. Calculated SHA-256 for `.packages/WT Otpravkapochtaru_3.0.0.zip`.
3. Inspected the rebuilt ZIP with `System.IO.Compression`.
4. Ran `git diff --check` before commit.
5. Created and pushed the source commit to `origin/main`.
6. Compared local `HEAD` with `origin/main`.

### Actual Results
1. Release package build passed and recreated `WT Otpravkapochtaru_3.0.0.zip`.
2. Package SHA-256 is `B20137F93DBCDBA12F27D063FA569E8753537DF8FAE219BAB66AB1350F56C9E2`.
3. ZIP inspection passed: 48 entries, manifest version `3.0.0`, expected source files present, `.webtolk` and `.packages` absent.
4. `git diff --check` passed.
5. Commit `d1e24d6` was pushed to `main`.
6. Local `HEAD` and `origin/main` both resolve to `d1e24d6992165d191dc0fb9fd6824edd3af073e3`.

### Residual Failures
- None in package rebuild, archive inspection, commit, or push verification.

## 2026-07-09 Exact Global InstallerScript Template Cases

### Executed Cases
1. Searched `D:\.agents` for installer templates and found `D:\.agents\templates\files\InstallerScript\script.php`.
2. Read the matching template README and language files.
3. Reapplied the installer message from the exact global template.
4. Searched target files for donor identifiers: `WTMAX`, `WT Max`, `wtmax`, `PKG_LIB_WTMAX`, `BRAND_`.
5. Searched `script.php` for direct output calls: `echo`, `print`, `var_dump`, `dump`.
6. Ran PHP syntax lint and product/test lint.
7. Ran PHPUnit.
8. Ran PHPStan.
9. Ran PHPCS.
10. Ran PHP CS Fixer dry-run.
11. Rebuilt the release package.
12. Inspected archived `script.php`.

### Actual Results
1. Exact global template path was confirmed.
2. Template language contract was adapted to `pkg_lib_wt_otpravkapochtaru.sys.ini`.
3. Target installer uses the template's logo image, community links, `WHATS_NEW`, and `MAYBE_INTERESTING` structure.
4. No donor identifiers or stale `BRAND_` keys were found in the target installer files.
5. No direct output calls were found in `script.php`.
6. PHP lint passed.
7. PHPUnit passed: `3 tests`, `4 assertions`.
8. PHPStan passed: no errors.
9. PHPCS passed.
10. PHP CS Fixer dry-run passed with no changed files and the known PHP runtime mismatch warning.
11. Package build passed.
12. Archived `script.php` has `EchoCount = 0`, `PrintCount = 0`, `EnqueueHtmlInfo = True`, `HasGlobalLogoImage = True`, `HasCommunityLinks = True`, `HasWhatsNew = True`, `HasMaybeInteresting = True`, `HasWtmaxDonor = False`.

### Residual Failures
- None in static QA and archive verification.
# 2026-07-11 Audit Cases

1. Joomla API existence check against 5.4.5 and 6.1.0: passed; no invented methods found.
2. Joomla deprecated API check: one finding, `Joomla\CMS\Http\HttpFactory`.
3. Dangerous primitive/raw input/TLS bypass search: passed with no matches.
4. Secret literal heuristic: passed with no hard-coded credential values.
5. PHP lint: passed for all product/test PHP files.
6. PHPUnit: passed, 3 tests / 4 assertions; coverage gap recorded.
7. PHPStan: passed at configured level 1 against Joomla 6.1 sources.
8. PHPCS: passed.
9. Composer JSON parse/platform/extension declarations: passed.
10. Composer CLI validation: not executed because local wrapper points to missing `\composer.phar`.

## 2026-07-11 Findings 1 And 2 Remediation Cases

1. Framework `Joomla\Http\HttpFactory::getHttp()` exists in Joomla 5.4.5 and 6.1.0: passed.
2. Deprecated `Joomla\CMS\Http\HttpFactory` import count: `0`.
3. Scratch filename verifier: passed `9/9` cases.
4. PHP lint for source and verifier: passed.
5. PHPUnit: passed `3/3`, `4` assertions.
6. PHPStan configured level 1: passed.
7. PHPCS for `Request.php`: passed.
8. PHP CS Fixer dry-run for `Request.php`: passed; PHP 8.3 runtime warning against PHP 8.1 target noted.
9. PhpStorm inspection: no new change-related errors/warnings.
10. Rebuilt ZIP: `48` entries; archived `Request.php` matches source and contains both fixes.
11. Final package SHA-256: `C68F71A6F4D6B7E207CDF8684C11BFA457D7A1934C2D90BACC5DD1A767E22C1A`.
12. Final tracked diff: exactly `lib_webtolk_otpravkapochtaru/src/Request.php`.

## 2026-07-11 Real REST Shipping Cases

1. Capture runner lint and PHPStorm inspection: passed; weak warnings only.
2. One live Joomla HTTP run: 29 REST calls, 25 ok, 4 errors, 0 skipped.
3. API-limit guard: passed, `getApiLimit=false`, local cap 40.
4. Tracking guard: passed, `tracking=false`.
5. Order lifecycle: create/edit/find by API ID/find by edited `order-num`/batch/find by RPO/return to `NEW`/delete passed.
6. Cleanup: `order_returned_to_new=true`, `order_deleted=true`.
7. Documents: ZIP and F103 calls both returned HTTP 400; no response schema fabricated.
8. Returns: direct returned `DIRECT_SHIPMENT_NOT_FOUND`; separate returned `FREE_ER_ADDRESS_NOT_ENABLED`.
9. Generator: 29 contracts, 27 examples, 27 schemas, 0 private-value leaks.
10. JSON parse: 55/55 files passed.
11. Example/schema validation: 27/27 passed.
12. Packaging boundary: 48 ZIP entries, 0 `docs/api-schemas/` entries.
13. Raw/verifier git-ignore: passed for all checked files.
14. Stand cleanup: temporary HTTP verifier removed.

## 2026-07-11 Technical Documentation Cases

1. Public facade coverage: 35/35 methods including constructor.
2. Low-level public coverage: 29/29 methods.
3. Entity public coverage: 17/17 methods.
4. Required method blocks: passed for every detailed facade section.
5. Standalone PHP examples: 60/60 passed syntax lint.
6. Relative links and explicit anchors: 140/140 resolved.
7. Markdown code fences: balanced in all 13 primary files.
8. PHPStorm errorsOnly inspection: 0 errors in all 13 primary files.
9. Regenerated response contracts: 27/27 examples validate; 0 private source-value leaks.
10. Git whitespace check: passed.
11. Shared Phing `3. Package release`: passed.
12. Final ZIP: 48 entries, 0 documentation entries, 3/3 manifests at `3.0.0`.
13. Archived/source `Request.php` SHA-256 parity: passed.
14. Final package SHA-256: `6B5B7746D97FCECA754A4E237A4AE18B505AD9674F5D801FFD70615FAF30253F`.

## 2026-07-11 Markdown Table Regression Cases

1. Union-type scan: 29 affected inline-code spans found and escaped in 8 files.
2. Table structure: 37/37 tables and 209/209 rows passed separator-count validation.
3. Inline code in tables: no unescaped pipe remains.
4. Full documentation verifier: 140 links and 60 PHP snippets passed; 0 errors.
5. PHPStorm `ERROR` inspection: 0 problems in the 8 affected documentation files and the verifier.
