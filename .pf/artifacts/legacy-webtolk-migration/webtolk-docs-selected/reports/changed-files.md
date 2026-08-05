# Changed Files

## 2026-07-10 Docblock Clarification Pass
- `composer.json` - added explicit `ext-simplexml` and `ext-soap` requirements after PhpStorm inspections reported missing extension metadata.
- `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php` - added meaningful facade method docblocks for REST, SOAP, payload normalization, tariff, post office, document and tracking methods.
- `lib_webtolk_otpravkapochtaru/src/Request.php` - documented REST transport behavior, URI/query normalization, JSON encoding/decoding, binary downloads and API error detection.
- `lib_webtolk_otpravkapochtaru/src/SoapRequest.php` - documented SOAP client creation and tracking credential access.
- `lib_webtolk_otpravkapochtaru/src/TrackingEntity.php` - documented single tracking, NPay, batch ticket chunking, ticket result loading and SOAP normalization.
- `lib_webtolk_otpravkapochtaru/src/Configuration/CredentialsProvider.php` - documented explicit/plugin parameter loading, auth modes and required credential validation.
- `lib_webtolk_otpravkapochtaru/src/Dictionaries/CountryDictionary.php` - added project file header and clarified dictionary row structure.
- `lib_webtolk_otpravkapochtaru/src/Entity/*.php` - documented entity hydration, defaults, required fields, nested entity conversion and API payload export.
- `lib_webtolk_otpravkapochtaru/src/Exception/*.php` - documented exception roles.
- `lib_webtolk_otpravkapochtaru/src/Fields/*.php` - documented Joomla admin form fields and inline error rendering behavior.
- `plg_system_wt_otpravkapochtaru/services/provider.php` - documented DI registration.
- `plg_system_wt_otpravkapochtaru/src/Extension/WtOtpravkapochtaru.php` - documented plugin shell/language loading role.
- `plg_system_wt_otpravkapochtaru/src/Field/PlugininfoField.php` - aligned field docblocks with the global PlugininfoField template intent while preserving current implementation.
- `script.php` - documented Joomla installer service provider, lifecycle hooks, compatibility checks, plugin auto-enable and queued branded message rendering.

## 2026-07-10 Global Template Use
- Consulted `D:\.agents\templates\files\PHP-doc-block-template\php-class-level-doc-bloc-template.md` for file/class-level docblock shape.
- Consulted `D:\.agents\templates\files\PlugininfoField\PlugininfoField.php` for Joomla note-field method docblock intent.
- Consulted `D:\.agents\templates\files\InstallerScript\script.php` for installer lifecycle structure and message handling context.

## Updated
- `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`
- `.webtolk/tmp/verify/joomla-local-api-sweep.php`
- `.webtolk/tmp/verify/joomla-local-unsupported-probe.php`
- `docs/briefs/development-flow-bootstrap.md`
- `docs/reports/development-scope-bootstrap.md`
- `docs/reports/implementation-plan.md`
- `docs/reports/decision-log.md`
- `docs/reports/release-notes.md`
- `docs/reports/migration-notes.md`
- `docs/reports/patch.md`
- `docs/reports/evolution-report.md`
- `docs/reports/donor-current-live-comparison.md`
- `docs/reports/change-summary.md`
- `docs/reports/changed-files.md`
- `.webtolk/evolutions/cursor.json`
- `.webtolk/patches/patch-20260422-1800-public-surface-prune.md`
- `.webtolk/logs/task-log.md`
- `.webtolk/logs/agent-log.md`
- `.webtolk/logs/verification-log.md`

## Removed
- `lib_webtolk_otpravkapochtaru/src/Exception/UnsupportedEndpointException.php`

## Structural Impact
- The public facade no longer exposes dead donor-era methods with no live contract.
- The read-only sweep now matches the actual supported public surface and no longer treats deleted methods as explicit skips.
- The unsupported-endpoint probe remains available only as direct evidence for raw legacy endpoint failure.

## Verification Impact
- Browser execution of `http://joomla.local/tmp/wt_otpravkapochtaru_api_sweep.php` now records `16 ok / 0 error / 14 skipped`.
- The new verification baseline represents the reduced public surface rather than a legacy-compatible one.

## 2026-07-09 QA Tooling Setup
- Added `.editorconfig`.
- Added `.php-cs-fixer.dist.php`.
- Added `phpcs.xml`.
- Added `phpstan.neon`.
- Added `phpunit.xml`.
- Added `composer.json`.
- Added `tools/qa/lint-php.ps1`.
- Added `tests/bootstrap.php`.
- Added `tests/Unit/Entity/OrderTest.php`.
- Added `tests/Unit/Dictionaries/CountryDictionaryTest.php`.
- Added `docs/reports/quality-tooling-setup.md`.
- Updated `.webtolk` logs and QA artifacts for the configured global-toolchain verification state.

## 2026-07-09 QA Tool Application
- Applied `php-cs-fixer` to product PHP source and tests.
- Applied `phpcbf` for PHPCS-auto-fixable style violations.
- Updated product formatting in:
  - `lib_webtolk_otpravkapochtaru/src/Configuration/CredentialsProvider.php`
  - `lib_webtolk_otpravkapochtaru/src/Dictionaries/CountryDictionary.php`
  - `lib_webtolk_otpravkapochtaru/src/Entity/CustomsDeclaration.php`
  - `lib_webtolk_otpravkapochtaru/src/Entity/CustomsDeclarationItem.php`
  - `lib_webtolk_otpravkapochtaru/src/Entity/EcomData.php`
  - `lib_webtolk_otpravkapochtaru/src/Entity/Item.php`
  - `lib_webtolk_otpravkapochtaru/src/Entity/Order.php`
  - `lib_webtolk_otpravkapochtaru/src/Entity/Recipient.php`
  - `lib_webtolk_otpravkapochtaru/src/Entity/ReturnShipment.php`
  - `lib_webtolk_otpravkapochtaru/src/Exception/OtpravkapochtaruException.php`
  - `lib_webtolk_otpravkapochtaru/src/Fields/AccountinfoField.php`
  - `lib_webtolk_otpravkapochtaru/src/Fields/OpslistField.php`
  - `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`
  - `lib_webtolk_otpravkapochtaru/src/Request.php`
  - `lib_webtolk_otpravkapochtaru/src/SoapRequest.php`
  - `lib_webtolk_otpravkapochtaru/src/TrackingEntity.php`
  - `plg_system_wt_otpravkapochtaru/services/provider.php`
  - `plg_system_wt_otpravkapochtaru/src/Field/PlugininfoField.php`
- Updated QA evidence artifacts and `.webtolk` logs for the now-green full quality gate.

## 2026-07-09 Documentation Rebuild
- Moved previous root documentation from `docs/` to `.webtolk/docs/root-docs-archive-20260709/`.
- Created new public documentation root:
  - `docs/README.md`
  - `docs/developer-api.md`
  - `docs/joomla-user-guide.md`
- Updated `.webtolk/docs/reports/artifact-index.md` so required flow artifacts point to `.webtolk/docs/reports/*`.
- Updated `.webtolk` logs for the documentation cycle.

## 2026-07-09 Documentation Review And Examples
- Added `docs/facade-method-reference.md`.
- Updated `docs/README.md` with a link to the practical facade method reference.
- Updated `docs/developer-api.md` with practical examples for entity normalization, low-level REST/SOAP classes and `CountryDictionary`.
- Updated `.webtolk` reports and logs with documentation review evidence.

## 2026-07-09 Public GitHub Repository Preparation
- Added root `README.md` with Russian overview, quick start, developer examples and documentation links.
- Added root `LICENSE` with GNU GPL v3.0 license text.
- Added `.gitignore` to keep internal process state, IDE files, packages and caches out of the public repository.
- Initialized local git repository on branch `main`.
- Prepared public GitHub repository target: `WebTolk/WT-Otpravkapochtaru-joomla-library`.

## 2026-07-09 WebTolk Installer And Docblocks
- Updated `script.php` to WebTolk/Joomla 4+ installer provider pattern with `InstallerScriptInterface`.
- Added installer preflight checks for minimum Joomla `5.0` and PHP `8.1`.
- Auto-enable `system/wt_otpravkapochtaru` on install, discover install and update.
- Added installer error language constants to:
  - `language/en-GB/pkg_lib_wt_otpravkapochtaru.sys.ini`
  - `language/ru-RU/pkg_lib_wt_otpravkapochtaru.sys.ini`
- Normalized WebTolk PHP file headers in product PHP files.

## 2026-07-10 Method-Level Since Tags
- Updated method docblocks in product PHP files under:
  - `lib_webtolk_otpravkapochtaru/src/**/*.php`
  - `plg_system_wt_otpravkapochtaru/services/provider.php`
  - `plg_system_wt_otpravkapochtaru/src/**/*.php`
  - `script.php`
- Added `@since 3.0.0` to newly written method docblocks that did not already have a method-level `@since`.
- Added `ext-soap` and `ext-simplexml` to `composer.json` during the preceding PhpStorm quality pass.
- Updated `.webtolk` reports and logs with the method-level `@since` verification evidence.

## 2026-07-10 Commit And Package Rebuild
- Rebuilt `.packages/WT Otpravkapochtaru_3.0.0.zip`.
- Pushed tracked source changes in commit `d1e24d6`.
- Updated `.webtolk` reports and logs with package hash and git publication evidence.
# 2026-07-11 Targeted Audit Remediation

- `lib_webtolk_otpravkapochtaru/src/Request.php` - replaced deprecated `Joomla\CMS\Http\HttpFactory` with `Joomla\Http\HttpFactory` and hardened server-provided filename normalization.
- `.packages/WT Otpravkapochtaru_3.0.0.zip` - rebuilt from the scoped source state; SHA-256 `C68F71A6F4D6B7E207CDF8684C11BFA457D7A1934C2D90BACC5DD1A767E22C1A`.
- No other tracked product files remain changed.

# 2026-07-25 Joomla-Style PHPDoc Cleanup

- `plg_system_wt_otpravkapochtaru/src/Field/PlugininfoField.php` - added property PHPDoc, return type declarations, `@throws`, and removed unused return assignment.
- `plg_system_wt_otpravkapochtaru/src/Extension/WtOtpravkapochtaru.php` - added Joomla-style property PHPDoc for `$autoloadLanguage`.
- `lib_webtolk_otpravkapochtaru/src/Entity/AbstractEntity.php` - added method-level `@param`, `@return`, `@since` and `@throws` tags for base helpers.
- `lib_webtolk_otpravkapochtaru/src/Dictionaries/CountryDictionary.php` - added class-level `@since`.
- `lib_webtolk_otpravkapochtaru/src/Configuration/CredentialsProvider.php` - added property PHPDoc and constructor parameter docs.
- `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php` - added property PHPDoc for REST and SOAP helper properties.
- `lib_webtolk_otpravkapochtaru/src/Fields/OpslistField.php` - added field property PHPDoc and `getOptions()` return docs.
- `lib_webtolk_otpravkapochtaru/src/Fields/AccountinfoField.php` - added field property PHPDoc and complete method return/parameter docs.
- `lib_webtolk_otpravkapochtaru/src/SoapRequest.php` - added constructor, return and SOAP fault docs.
- `script.php` - added Joomla-style property and lifecycle method PHPDoc in the installer provider.
- `.packages/WT Otpravkapochtaru_3.0.0.zip` - rebuilt from the updated source; SHA-256 `FA4B34B34C826DDE56481D761FD280E7AE19E6C1D0D5DC43D60177CFD16EBF53`.
- `.webtolk/docs/reports/*.md`, `.webtolk/logs/*.md` - refreshed process evidence for the PHPDoc cleanup and package rebuild.

# 2026-07-25 PHPDoc Delivery

- Committed and pushed tracked source files from the PHPDoc cleanup:
  - `script.php`
  - `plg_system_wt_otpravkapochtaru/src/Extension/WtOtpravkapochtaru.php`
  - `plg_system_wt_otpravkapochtaru/src/Field/PlugininfoField.php`
  - `lib_webtolk_otpravkapochtaru/src/Configuration/CredentialsProvider.php`
  - `lib_webtolk_otpravkapochtaru/src/Dictionaries/CountryDictionary.php`
  - `lib_webtolk_otpravkapochtaru/src/Entity/AbstractEntity.php`
  - `lib_webtolk_otpravkapochtaru/src/Fields/AccountinfoField.php`
  - `lib_webtolk_otpravkapochtaru/src/Fields/OpslistField.php`
  - `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`
  - `lib_webtolk_otpravkapochtaru/src/SoapRequest.php`
- Commit: `541a8e9d9af39f199c0274c837eb8b901fa27865`.

# 2026-07-11 Real REST API Schema Appendix

- `docs/README.md` - linked the observed REST response appendix.
- `docs/api-schemas/otpravka/README.md` - documented test scope, route, results, errors, anonymization and limitations.
- `docs/api-schemas/otpravka/index.json` - added 29-method observed-contract index.
- `docs/api-schemas/otpravka/examples/*.response.json` - added 27 anonymized real response examples.
- `docs/api-schemas/otpravka/schemas/*.response.schema.json` - added 27 inferred Draft 2020-12 schemas.
- `.webtolk/tmp/rest-api-capture-20260711/` - raw local capture, intentionally ignored.
- `.webtolk/tmp/verify/*api-schemas.php` and `joomla-local-shipping-api-capture.php` - ignored research/verifier scripts.
- Product source was not changed by this live-test slice; the earlier uncommitted `Request.php` remediation remains present.

# 2026-07-11 Full Technical Documentation

- `README.md` - replaced the minimal quick start with installation, normalized tariff calculation and order creation scenarios.
- `docs/README.md` - rebuilt as the documentation index.
- `docs/developer-api.md` - architecture, configuration, data types, exceptions, limits, security and integration sequence.
- `docs/facade-method-reference.md` - complete map of the facade's public surface.
- `docs/api/*.md` - 7 new thematic chapters with method-level descriptions, types, schemas and standalone examples.
- `docs/entities-reference.md` - public entity factories/export methods, required fields and defaults.
- `docs/low-level-api.md` - complete public reference for credentials, REST, SOAP, tracking and country dictionary classes.
- `docs/api-schemas/otpravka/**` - regenerated derived examples after stricter FIO/GUID anonymization.
- Product source unchanged by the documentation task; previous `Request.php` remediation remains separate.
- `.packages/WT Otpravkapochtaru_3.0.0.zip` - rebuilt by shared Phing packaging tool; SHA-256 `6B5B7746D97FCECA754A4E237A4AE18B505AD9674F5D801FFD70615FAF30253F`.

# 2026-07-11 Markdown Table Correction

- `docs/facade-method-reference.md`
- `docs/low-level-api.md`
- `docs/api/account-and-configuration.md`
- `docs/api/batches-and-documents.md`
- `docs/api/normalization-and-tariffs.md`
- `docs/api/orders.md`
- `docs/api/post-offices-and-dictionaries.md`
- `docs/api/returns.md`
- `.webtolk/tmp/verify/technical-documentation-check.php` - added table structure regression checks.

# 2026-07-11 Documentation Commit And Push

- Committed and pushed: `README.md`, `docs/README.md`, `docs/developer-api.md`, `docs/facade-method-reference.md`, `docs/api/*.md`, `docs/entities-reference.md`, `docs/low-level-api.md`, `docs/api-schemas/otpravka/**/*`.
- Commit: `3a8c9144033f5fb91562b7dce12b69150828a09a`.
- Not committed: `lib_webtolk_otpravkapochtaru/src/Request.php`.
- Updated ignored flow artifacts: `.webtolk/docs/reports/{task-record,stage-decision,change-summary,changed-files,next-skill-handoff}.md`, `.webtolk/logs/{task-log,verification-log}.md`.

# 2026-07-11 Request Transport Commit And Package Rebuild

- Committed and pushed: `lib_webtolk_otpravkapochtaru/src/Request.php`.
- Rebuilt ignored package artifact: `.packages/WT Otpravkapochtaru_3.0.0.zip`.
- Commit: `ee582cd51db5b5572d0d291ed7214beed73dd021`.
- Updated ignored flow artifacts: `.webtolk/docs/reports/{task-record,stage-decision,change-summary,changed-files,next-skill-handoff}.md`, `.webtolk/logs/{task-log,verification-log}.md`.

# 2026-07-11 SW JProjects Update Metadata

- Committed and pushed: `pkg_lib_wt_otpravkapochtaru.xml`.
- Rebuilt ignored package artifact: `.packages/WT Otpravkapochtaru_3.0.0.zip`.
- Created remote CMS item: SW JProjects project ID `119`.
- Commit: `0596f132efbf1af6e9baff0021604541fcb08024`.
- Updated ignored flow artifacts: `.webtolk/docs/reports/{task-record,stage-decision,change-summary,changed-files,next-skill-handoff}.md`, `.webtolk/logs/{task-log,verification-log}.md`.

# 2026-07-11 Plugin Settings Screenshots

- Created ignored documentation support files: `.webtolk/tmp/screenshots/plugin-settings-en-GB-1920x1080.png`, `.webtolk/tmp/screenshots/plugin-settings-ru-RU-1920x1080.png`.
- Updated ignored flow artifacts: `.webtolk/logs/{task-log,verification-log}.md`, `.webtolk/docs/reports/changed-files.md`.

# 2026-07-11 WebTolk Screenshot Upload

- Uploaded remote SW JProjects gallery files:
  - `https://web-tolk.ru/images/swjprojects/projects/119/ru-RU/gallery/hAGE8nogttb.png`
  - `https://web-tolk.ru/images/swjprojects/projects/119/en-GB/gallery/N6LXcAvFTt0.png`
- Updated ignored flow artifacts: `.webtolk/logs/{task-log,verification-log}.md`, `.webtolk/docs/reports/changed-files.md`.

# 2026-07-11 SW JProjects Publication Documentation Draft

- Created local publication artifacts under `.webtolk/tmp/swjprojects-publication-docs-20260711/`:
  - `publication-docs-ru.md`
  - `publication-docs-en.md`
  - `publication-docs-ru.html`
  - `publication-docs-en.html`
  - `official-structure-comparison.md`
  - `publication-payload.json`
  - `artifact-index.md`
- Updated ignored flow artifacts: `.webtolk/logs/{agent-log,task-log,verification-log}.md`, `.webtolk/docs/reports/{artifact-index,task-record,changed-files,change-summary,stage-decision,next-skill-handoff}.md`.

# 2026-07-25 Development-Flow Re-Entry

- Updated ignored flow artifacts only:
  - `.webtolk/docs/reports/artifact-index.md`
  - `.webtolk/docs/reports/task-record.md`
  - `.webtolk/docs/reports/stage-decision.md`
  - `.webtolk/docs/reports/next-skill-handoff.md`
  - `.webtolk/docs/reports/change-summary.md`
  - `.webtolk/docs/reports/changed-files.md`
  - `.webtolk/logs/task-log.md`
  - `.webtolk/logs/agent-log.md`
  - `.webtolk/logs/verification-log.md`
  - `.webtolk/logs/tool-telemetry.ndjson`
- Product source files changed: none.
- Package files changed: none.

# 2026-07-25 Package Rebuild

- Updated ignored project-local packaging config:
  - `.webtolk/build/package.config.json`
- Rebuilt ignored package artifact:
  - `.packages/WT Otpravkapochtaru_3.0.0.zip`
- Updated ignored flow artifacts:
  - `.webtolk/docs/reports/release-notes.md`
  - `.webtolk/docs/reports/migration-notes.md`
  - `.webtolk/docs/reports/artifact-index.md`
  - `.webtolk/docs/reports/task-record.md`
  - `.webtolk/docs/reports/stage-decision.md`
  - `.webtolk/docs/reports/next-skill-handoff.md`
  - `.webtolk/docs/reports/change-summary.md`
  - `.webtolk/docs/reports/changed-files.md`
  - `.webtolk/logs/task-log.md`
  - `.webtolk/logs/agent-log.md`
  - `.webtolk/logs/verification-log.md`
  - `.webtolk/logs/tool-telemetry.ndjson`
- Product source files changed: none.
- Remote CMS files changed: none.

# 2026-07-25 Test Order And Tracking Runtime Assurance

- Added ignored runtime script:
  - `.webtolk/tmp/verify/joomla-local-create-order-and-tracking-20260725.php`
- Created ignored runtime evidence:
  - `.webtolk/tmp/order-tracking-check-20260725/*.json`
- Added/updated ignored flow artifacts:
  - `.webtolk/docs/reports/order-tracking-runtime-assurance-20260725.md`
  - `.webtolk/docs/reports/artifact-index.md`
  - `.webtolk/docs/reports/task-record.md`
  - `.webtolk/docs/reports/stage-decision.md`
  - `.webtolk/docs/reports/next-skill-handoff.md`
  - `.webtolk/docs/reports/change-summary.md`
  - `.webtolk/docs/reports/changed-files.md`
  - `.webtolk/logs/task-log.md`
  - `.webtolk/logs/agent-log.md`
  - `.webtolk/logs/verification-log.md`
  - `.webtolk/logs/tool-telemetry.ndjson`
- Product source files changed: none.
- Package files changed: none.
