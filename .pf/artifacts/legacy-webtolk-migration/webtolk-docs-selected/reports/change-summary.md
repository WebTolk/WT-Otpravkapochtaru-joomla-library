# Change Summary

## 2026-07-10 Docblock Clarification Pass
- Added meaningful file/class and method docblocks across the public facade, transport, SOAP tracking, credentials, entity payloads, Joomla fields, plugin provider and installer script.
- Used global docblock-related templates from `D:\.agents\templates\files\` where they fit the local file type: PHP class-level template, PlugininfoField template and InstallerScript template.
- Kept changes documentation-only: no method signatures, logic, endpoint paths, payload construction, package metadata or runtime behavior were changed.
- Focused descriptions on what each method does and, where logic is non-trivial, documented the relevant behavior: batch tracking chunks, SOAP payload shape, API/business error handling, query normalization, entity defaults and validation.

## 2026-07-10 Verification
- PhpStorm project config checked: IDE language level `8.1`, interpreter PHP `8.3.30`, SOAP and SimpleXML extensions loaded.
- PhpStorm inspections were run on the main changed files. `WARNING+` findings for missing `ext-soap` and `ext-simplexml` in `composer.json` were fixed by adding explicit Composer platform requirements.
- PhpStorm `WARNING+` recheck passed for `SoapRequest.php`, `TrackingEntity.php`, `PlugininfoField.php`, `CredentialsProvider.php`, `Order.php`, `AccountinfoField.php`, `AbstractEntity.php`, `ReturnShipment.php`, `CountryDictionary.php`, `OpslistField.php`, plugin provider and plugin extension.
- PHP syntax check passed for every changed PHP file.
- `tools/qa/lint-php.ps1` passed.
- PHP CS Fixer dry-run passed through `D:/.agents/tools/php-qa/vendor/bin/php-cs-fixer` with the known PHP 8.3 runtime warning.
- PHPCS passed through `D:/.agents/tools/php-qa/vendor/bin/phpcs`.
- PHPStan passed with no errors.
- PHPUnit passed: `3 tests / 4 assertions`.

## What changed
- Removed `getBalance()`, `getCategoryList()`, `getCategoryDescription()`, and `getObjectInfo()` from the public facade in `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`.
- Removed the no-longer-needed `UnsupportedEndpointException`.
- Updated `.webtolk/tmp/verify/joomla-local-api-sweep.php` so the read-only runner matches the reduced public surface.
- Kept `.webtolk/tmp/verify/joomla-local-unsupported-probe.php` only as raw evidence for legacy endpoint failure.
- Synced the updated facade and sweep wrapper to `joomla.local` and reran browser verification.
- Updated release, patch, evolution and `.webtolk` logs for cycle closure.

## Why this change
- Donor review and live probes confirmed that the four removed methods were only wrappers around dead endpoints.
- This library is not required to preserve donor backward compatibility.
- Keeping dead methods as unsupported stubs would leave historical noise in both the public API and the verification baseline.

## Current status
- The non-tracking browser sweep now completes against `joomla.local` with `30` methods executed: `16 ok`, `0 error`, `14 skipped`.
- The verified working facade now contains only methods with a justified current role: account/settings, recipient reliability, tariff calculation, country dictionary, postoffice lookups, and read-only order/batch lookups.
- Remaining skipped methods are mutation-dependent operations in the default read-only runner.

## 2026-07-09 QA Tooling Setup
- Configured project-local QA entrypoints that use global tools from `D:/.agents/tools/php-qa`.
- Added PHPUnit bootstrap/autoload support for project classes and Joomla 6.1 core mirror classes without adding local `vendor/`.
- Added smoke unit tests for entity payload normalization and the local country dictionary.
- Verified syntax lint, PHPUnit and PHPStan successfully through direct global binary invocations.
- Full style gates are now runnable and currently expose existing product-code formatting debt.

## 2026-07-09 QA Tool Application
- Ran the configured global QA tools as separate tasks: PHP lint, PHPUnit, PHPStan, PHP CS Fixer, PHPCS, and PHPCBF.
- Applied code style fixes across the existing PHP source using `php-cs-fixer`.
- Applied PHPCS auto-fixes with `phpcbf` for the remaining brace and union-spacing violations.
- Re-ran the full gate after fixes: PHP lint, PHPUnit, PHPStan, PHP CS Fixer dry-run, and PHPCS all pass.

## Current QA Status
- Syntax gate: green.
- Unit tests: green, `3 tests / 4 assertions`.
- Static analysis: green, no PHPStan errors.
- Formatting gate: green after automatic fixes.
- Coding standard gate: green after automatic fixes.

## 2026-07-09 Documentation Rebuild
- The old root `docs/` folder was moved into `.webtolk/docs/root-docs-archive-20260709/`.
- A new root `docs/` folder was created for public-facing documentation only.
- Added Russian developer documentation covering the public facade, entity classes, credential provider, low-level REST/SOAP classes, dictionary and exceptions.
- Added Russian Joomla user documentation covering installation, plugin settings, account-status check, tracking credentials and common configuration errors.
- Flow artifact paths were normalized in `.webtolk/docs/reports/artifact-index.md` after the move.

## 2026-07-09 Documentation Review And Examples
- Reviewed the new documentation against the requirement: examples of code, data structures, method behavior and method purpose.
- Found that the first pass listed all methods but did not provide a practical example for every facade method.
- Added a dedicated Russian practical facade reference with `Что делает`, `Зачем нужен`, code examples and typical data structures for every public facade method.
- Expanded developer documentation with examples for entity payload normalization and low-level public helper classes.

## 2026-07-09 Public GitHub Repository Preparation
- Prepared the project for publication as a public WebTolk GitHub repository.
- Added root README with project purpose, requirements, quick start, code examples, documentation links and QA commands.
- Added GPL-3.0 license text in `LICENSE`.
- Added `.gitignore` so `.webtolk`, `.packages`, `.idea`, `.serena`, caches and generated archives do not get published.
- Restored local git state by initializing the previously empty `.git` directory on branch `main`.
- Created public repository `https://github.com/WebTolk/WT-Otpravkapochtaru-joomla-library`.
- Pushed commit `b4b9a83` (`Initial public release`) to `main`.

## 2026-07-09 WebTolk Installer And Docblocks
- Reworked `script.php` into a fuller WebTolk-style Joomla installer provider.
- Installer now blocks installation when Joomla is older than `5.0` or PHP is older than `8.1`.
- Installer enables the system plugin during install, discover install and update.
- Added English and Russian language strings for minimum-version installer errors.
- Standardized product PHP file headers to the WebTolk block:
  `@package`, `@version`, `@author`, `@copyright`, `@license`, `@since`.
- Pushed commit `08b53af` (`Add WebTolk installer checks`) to the public GitHub repository.

## 2026-07-09 Installer Message Output Fix
- Removed direct `echo` output from `script.php` installer `postflight()`.
- Installer now builds the HTML message string and passes it to `$this->app->enqueueMessage($html, 'info')`.
- Rebuilt the release ZIP and verified the archived `script.php` contains no `echo` and contains one `enqueueMessage($html, 'info')` call.

## 2026-07-09 Branded WebTolk Installer Message
- Adapted the installer message to the global WebTolk branded post-installation pattern from `D:\.agents\docs\joomla-development-articles\web-tolk\brendirovanie-rasshirenij-dlya-joomla-razrabotchikov\index.md`.
- `script.php` now renders a branded HTML message with WebTolk logo, package version, action badge, short product description, feature bullets and useful links.
- The installer still does not use direct output: `postflight()` builds `$html` and sends it through `$this->app->enqueueMessage($html, 'info')`.
- Added English and Russian language constants for the branded installer message.
- Rebuilt `.packages/WT Otpravkapochtaru_3.0.0.zip` and verified the archived `script.php` contains the branded renderer, no `echo`, no `print`, and the expected Joomla message queue call.
- Pushed commit `c2f7b32` (`Use branded WebTolk installer message`) to `main`.

## 2026-07-09 Global InstallerScript Template Reapplication
- Found the exact global copy-adapt template at `D:\.agents\templates\files\InstallerScript\script.php` and its matching language examples under `D:\.agents\templates\files\InstallerScript\language\`.
- Reworked `script.php` from the actual global template structure: WebTolk logo image, `Joomla Extensions` block, website/email buttons, community buttons, `WHATS_NEW`, and `MAYBE_INTERESTING`.
- Replaced donor identifiers and language prefix from `PKG_LIB_WTMAX` / `wtmax` with `PKG_LIB_WT_OTPRAVKAPOCHTARU` / `wt_otpravkapochtaru`.
- Kept the existing project-specific behavior: Joomla/PHP compatibility checks and auto-enabling the `system/wt_otpravkapochtaru` plugin.
- Rebuilt `.packages/WT Otpravkapochtaru_3.0.0.zip`; archive inspection confirmed no direct output, template branding is present, and donor `WTMAX` identifiers are absent.

## 2026-07-09 Installer Package Name Language Fix
- Changed the base package name constant in both package sys language files to `WT Otpravkapochtaru`.
- Rebuilt `.packages/WT Otpravkapochtaru_3.0.0.zip`; archive inspection confirmed both language files start with `PKG_LIB_WT_OTPRAVKAPOCHTARU="WT Otpravkapochtaru"`.

## 2026-07-09 Post-Install Description Update
- Updated post-install description texts in English and Russian package sys language files.
- The post-install screen now describes the extension by functional value: delivery cost calculation, shipment creation and management, batch/document operations, postal office lookup and tracking.
- The settings block was shortened to a single practical note about entering the required Russian Post API credentials in the system plugin.
- Rebuilt `.packages/WT Otpravkapochtaru_3.0.0.zip`; archive inspection confirmed the new delivery/shipment/tracking wording is present in both language files.

## 2026-07-10 Method-Level Since Tags
- Added method-level `@since 3.0.0` tags to newly written method docblocks, including `public static` factory methods.
- Kept pre-existing method `@since` values where the docblocks already existed, for example the Joomla form field methods that already carried `@since 1.7.0`.
- Normalized PHPDoc indentation and LF line endings after the mechanical insertion.
- No runtime behavior was changed.
- PhpStorm inspections no longer report missing method-level `@since` in the checked files; remaining weak warnings are unrelated pre-existing items such as duplicate definitions from `.webtolk/tmp` copies and dynamic SOAP methods.
- CLI QA is green: syntax lint, `php-cs-fixer --dry-run`, PHPCS, PHPStan, PHPUnit, and `git diff --check` passed.

## 2026-07-10 Method Docblocks Commit And Package Rebuild
- Rebuilt `.packages/WT Otpravkapochtaru_3.0.0.zip` with `phing -f D:\Dev\WT-Otpravkapochtaru-joomla-library\phing.xml "3. Package release"`.
- Verified the rebuilt archive has 48 entries, package manifest version `3.0.0`, `script.php`, `composer.json`, `ext-soap`, `ext-simplexml`, method-level `@since 3.0.0`, and no `.webtolk` or `.packages` entries.
- Package SHA-256: `B20137F93DBCDBA12F27D063FA569E8753537DF8FAE219BAB66AB1350F56C9E2`.
- Committed tracked source changes as `d1e24d6` (`Add method docblocks and PHP extension requirements`) and pushed `main` to `origin/main`.
- Local `HEAD` and `origin/main` both resolve to `d1e24d6992165d191dc0fb9fd6824edd3af073e3`.
# 2026-07-11 Findings 1 And 2 Remediation

- Migrated REST client creation to the non-deprecated framework `Joomla\Http\HttpFactory`, available in local Joomla 5.4.5 and 6.1.0 cores.
- Hardened `Content-Disposition` filename handling against traversal paths, Windows separators, control/forbidden characters, trailing dots/spaces, empty names, and reserved device names.
- Left all other audit findings unchanged.
- Rebuilt and inspected package `3.0.0`; archived `Request.php` matches the source.

# 2026-07-11 Real REST API Assurance And Documentation

- Ran one real shipping REST scenario on Joomla for `410000 Саратов` -> `685000 Магадан`.
- Used 29 requests without querying the API balance and without tracking calls.
- Confirmed 25 operations, including order creation, edit, both lookups, batch flow, return to `NEW`, and deletion.
- Captured four real failure paths: two HTTP 400 document calls and two return-service business errors.
- Published only anonymized derived artifacts: 27 examples, 27 observed schemas and a 29-contract index.
- Kept raw account-bearing captures inside ignored `.webtolk/tmp/`; verified the ZIP still excludes documentation.

# 2026-07-11 Full Technical Documentation

- Replaced the monolithic and partly stale method guide with a structured technical manual.
- Documented every public facade method with purpose, rationale, implementation path, types, response contract and copy-ready code.
- Added complete references for all public low-level and entity methods.
- Made address/FIO/phone normalization part of the recommended tariff and order flows.
- Connected documented responses to real anonymized examples and observed JSON Schemas.
- Added a complete README quick start and rebuilt the documentation index.
- Rebuilt the Joomla package through the configured shared Phing target; documentation stayed excluded and archive source parity passed.

# 2026-07-11 Markdown Table Correction

- Escaped union-type pipes in Markdown table cells so method signatures and data types render as single code spans.
- Added structural table validation and a regression rule for unescaped pipes inside inline code.
- No library source, API behavior, JSON schema or package content was changed.

# 2026-07-11 Documentation Commit And Push

- Published the completed documentation and API schema appendix in commit `3a8c9144033f5fb91562b7dce12b69150828a09a`.
- Pushed `main` to `origin/main`; local and remote refs match after delivery.
- Kept the separate product-code remediation in `lib_webtolk_otpravkapochtaru/src/Request.php` out of this documentation commit.
- Package archive was not rebuilt during this delivery step because the committed files are documentation and schema appendix files excluded from the Joomla package.

# 2026-07-11 Request Transport Commit And Package Rebuild

- Published `lib_webtolk_otpravkapochtaru/src/Request.php` in commit `ee582cd51db5b5572d0d291ed7214beed73dd021`.
- Replaced deprecated `Joomla\CMS\Http\HttpFactory` usage with `Joomla\Http\HttpFactory`.
- Hardened server-provided filename normalization for attachment downloads.
- Rebuilt `.packages/WT Otpravkapochtaru_3.0.0.zip`; SHA-256 `99EBAD7F571B80DAFBDE5A333A1DF66D317D2723BC25AC3723EB07185834E083`.
- Verified the archive contains the updated `Request.php` and excludes documentation/process folders.

# 2026-07-11 SW JProjects Update Metadata

- Created unpublished SW JProjects project `119` for `WT Otpravkapochtaru`.
- Filled Russian and English title, descriptions, payment/free status, Joomla package metadata, update-server settings and metadata fields.
- Added update server and changelog URLs to `pkg_lib_wt_otpravkapochtaru.xml`.
- Rebuilt `.packages/WT Otpravkapochtaru_3.0.0.zip`; SHA-256 `567BC3416686FE7FC04BF8886282E82D79608FE619D136449C83AA828B40E86F`.
- Published commit `0596f132efbf1af6e9baff0021604541fcb08024`.

# 2026-07-11 SW JProjects Publication Documentation Draft

- Compared current documentation structure with the official Otpravka API specification.
- Prepared Russian and English publication documentation for the SW JProjects project page.
- Converted both language versions to HTML fragments for later manual insertion into SW JProjects.
- Added local comparison and payload artifacts under `.webtolk/tmp/swjprojects-publication-docs-20260711/`.
- Did not publish or modify the remote project.

# 2026-07-25 Development-Flow Re-Entry

- Re-entered the project through `.webtolk` flow and reloaded Joomla platform knowledge from `D:/.agents`.
- Reconciled prior memory and `.webtolk` artifacts with live Git and package state.
- Confirmed the current stop point is local-only SW JProjects publication preparation, not the older docblock/package slice.
- Product source, package manifest, package ZIP and remote CMS state were not changed.
- Updated required process artifacts and logs with the 2026-07-25 status boundary.

# 2026-07-25 Test Order And Tracking Runtime Assurance

- Added a local ignored runtime script for creating a fresh test order and checking tracking from the same barcode.
- Created order `2333724273` with order number `codex-order-tracking-20260725_183153`.
- Verified barcode/RPO `80214523462306` with `getOperationsByRpo`; SOAP returned one operation-history record.
- Verified plugin tracking credentials are present without exposing their values.
- Recorded that `getTickets` returned `not_create` for the fresh barcode, so `getOperationsByTicket` was not exercised.
- No product source, package XML, package ZIP or remote CMS state was changed.

# 2026-07-25 Package Rebuild

- Rebuilt the package through `phing.xml` and the shared Phing packager.
- Detected and fixed a packaging boundary issue: `.playwright-mcp/` was not excluded and one console log entered the first ZIP.
- Added `.playwright-mcp/` to `.webtolk/build/package.config.json` excludes.
- Rebuilt the package again and verified the final ZIP is clean.
- Final SHA-256: `D5AEE6C1373E5CE72CDFF531F6CA13274267FCCF0734E183CDE5B94AC193484E`.

# 2026-07-25 Joomla-Style PHPDoc Cleanup

- Read PhpStorm inspections for the product PHP files and fixed the docblock-focused findings.
- Used Context7 Joomla Coding Standards/Manual and local Joomla documentation as the PHPDoc style reference.
- Added missing `@var`, `@param`, `@return`, `@since` and `@throws` tags on class properties and methods in the inspected surface.
- Added return declarations to `PlugininfoField::getInput()`, `getLabel()` and `getTitle()`.
- Kept project PHP-CS-Fixer preferences for global PHP classes, even where PhpStorm suggests imports.
- Rebuilt the release package; final SHA-256: `FA4B34B34C826DDE56481D761FD280E7AE19E6C1D0D5DC43D60177CFD16EBF53`.

# 2026-07-25 PHPDoc Commit And Push

- Committed the tracked PHPDoc cleanup in `541a8e9d9af39f199c0274c837eb8b901fa27865`.
- Pushed `main` to `origin/main`; local and remote refs match.
- Ignored `.webtolk` reports/logs and `.packages` ZIP were not committed.
