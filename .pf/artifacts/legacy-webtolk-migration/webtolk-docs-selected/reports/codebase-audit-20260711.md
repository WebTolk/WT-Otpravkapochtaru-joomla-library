# Codebase Audit - 2026-07-11

## Scope

- Product code under `lib_webtolk_otpravkapochtaru/`, `plg_system_wt_otpravkapochtaru/`, and root `script.php`.
- Joomla API compatibility against local Joomla `5.4.5`, `6.0.4`, and `6.1.0` core snapshots.
- Deprecated or invented Joomla API, architecture/overengineering, security, performance, and test assurance.
- Research only. No product PHP, XML, manifest, package, or test code was changed.

## Knowledge Sources

- `D:/.agents/platforms/joomla/platform.json`
- `D:/.agents/docs/joomla-toolkit/README.md`
- `D:/.agents/docs/joomla-toolkit/joomla-architecture-rules.md`
- `D:/Dev/Joomla-documentation/docs-new/basics/joomla-internals/deprecated-metody-yadra-joomla-6-1.md`
- `D:/Dev/Joomla-documentation/docs-new/cms/http/http-factory.md`
- `D:/Dev/Joomla-documentation/docs-new/cms/http/http.md`
- `D:/Dev/Joomla-documentation/docs-new/cms/http/kak-yadro-joomla-vypolnyaet-http-zaprosy.md`
- `D:/Dev/Joomla-documentation/docs-new/cms/form/form-field.md`
- `D:/Dev/Joomla-documentation/docs-new/cms/form/field-note-field.md`
- `D:/Dev/Joomla-documentation/docs-new/cms/form/field-list-field.md`
- Joomla core snapshots under `D:/.agents/docs/Joomla-core/5.x/5.4.5`, `6.x/6.0.4`, and `6.x/6.1.0`.

## Executive Summary

- Critical findings: 0.
- High findings: 0.
- Medium findings: 5.
- Low findings: 5.
- No invented/non-existent Joomla method calls were found.
- One Joomla API usage is deprecated in Joomla 6 and scheduled for removal in Joomla 7.
- Existing read-only QA passes, but coverage is too narrow to provide strong assurance for transport, tracking, installer, fields, and credential handling.

## Findings

### MEDIUM-01 - Deprecated Joomla CMS HTTP factory

- Evidence: `lib_webtolk_otpravkapochtaru/src/Request.php:18,181-184` imports and instantiates `Joomla\CMS\Http\HttpFactory`.
- Joomla evidence: `libraries/src/Http/HttpFactory.php:26,41` in Joomla 6.0.4/6.1.0 marks the class and `getHttp()` deprecated since 6.0, removal in Joomla 7, replacement `Joomla\Http\HttpFactory`.
- Impact: works on Joomla 5/6 today but creates a known forward-compatibility break for a package advertised as Joomla 5+.
- Recommendation: use `Joomla\Http\HttpFactory`, preferably injected once into `Request`, and return/type against `Joomla\Http\Http`.

### MEDIUM-02 - Download filename is not actually sanitized

- Evidence: `Request.php:336-366` extracts `filename` / `filename*`, but `sanitizeFileName()` only trims quotes.
- Impact: values such as `../name.pdf`, `..\\name.pdf`, control characters, reserved device names, or absolute-looking paths are returned as a supposedly safe filename. A consumer that writes the document using this value can introduce path traversal or unsafe file creation.
- Boundary: the current upstream hosts are fixed HTTPS Russian Post endpoints, which lowers exploitability, but the library exposes the filename as a reusable security-sensitive contract.
- Recommendation: remove directory components, reject control/NUL characters, normalize length and extension, and document that callers must choose the final storage path independently.

### MEDIUM-03 - Administrator form rendering performs blocking remote calls

- Evidence: `AccountinfoField.php:38-90` performs one or two synchronous API calls during field rendering; `OpslistField.php:36-53` performs another remote call while building options.
- Timeout evidence: `CredentialsProvider.php:134-138` accepts an integer timeout; plugin manifest/config only declares `min="1"` and no maximum. Default is 60 seconds.
- Impact: opening the plugin edit form can block for roughly two sequential timeouts, and an administrator can configure a much larger value. Remote degradation becomes Joomla administrator-page degradation.
- Recommendation: do not call external APIs in normal form rendering. Use an explicit AJAX/status action with ACL + CSRF checks, or cache short-lived status/shipping-point results. Enforce a conservative maximum timeout.

### MEDIUM-04 - Transport and security-critical code lacks focused tests

- Evidence: PHPUnit executes 3 tests / 4 assertions covering only `Order` and `CountryDictionary`. There are no unit tests for `Request`, filename handling, error classification, `CredentialsProvider`, SOAP payloads, tracking chunking, installer/provider contracts, or FormField escaping/failure modes.
- Static-analysis boundary: PHPStan passes at level 1 and scans Joomla 6.1 only.
- Impact: the highest-risk code paths can regress while all current automated gates remain green; Joomla 5 compatibility is established mainly by manual core inspection rather than executable tests.
- Recommendation: prioritize deterministic tests around the transport boundary and security transformations; add a Joomla 5.4 and 6.1 compatibility matrix or dual static-analysis configuration.

### MEDIUM-05 - Credential-like values are rendered as plain text fields

- Evidence: `plg_system_wt_otpravkapochtaru/wt_otpravkapochtaru.xml:35-56` and duplicated `config/config.xml:17-38` declare `access_token` and `user_key` as `type="text"` although both are sent as authentication credentials.
- Impact: credentials are visibly exposed in the administrator UI and are more likely to be copied, captured in screenshots, or retained by browser tooling. Joomla extension params remain server-side configuration, so changing field type is masking rather than encryption, but it reduces accidental disclosure.
- Recommendation: use password-style fields with deliberate reveal/replace semantics and appropriate autocomplete handling; document storage limitations and restrict plugin configuration ACL.

### LOW-01 - SOAP trace retains credential-bearing request data

- Evidence: `SoapRequest.php:80-90` enables `trace => 1`; tracking payloads contain login/password at `TrackingEntity.php:173-193` and `210-219`.
- Impact: the SOAP client retains the last request/response in memory, including credentials, without production code using trace diagnostics. It also adds avoidable memory overhead.
- Recommendation: disable trace by default and enable only through an explicit debug option that never logs authorization payloads.

### LOW-02 - HTTP and SOAP clients are repeatedly instantiated

- Evidence: `Request.php:181-184` constructs a factory/client for every REST call; `TrackingEntity.php:144-156` creates a SOAP client per method call, including once per batch chunk in `getTickets()`.
- Impact: repeated transport setup and SOAP/WSDL client construction adds latency and allocation overhead. WSDL caching mitigates but does not eliminate the cost.
- Recommendation: inject and reuse the HTTP factory/client; lazily cache single and pack SOAP clients inside `SoapRequest` or `TrackingEntity`.

### LOW-03 - Binary responses are fully buffered without a size guard

- Evidence: `Request.php:158-173` casts the full response body to string and returns it in memory.
- Impact: unusually large or malformed upstream documents can cause high peak memory usage. The fixed trusted hosts reduce hostile control but not operational risk.
- Recommendation: expose a streaming/download-to-resource option or enforce a documented maximum response size.

### LOW-04 - Plugin information field trusts manifest XML output

- Evidence: `PlugininfoField.php:46-68` reads a path assembled from form `folder`/`element`, does not handle `simplexml_load_file()` failure, and inserts manifest `version`/`description` into administrator HTML without escaping.
- Impact: a malformed/missing manifest can break the field; a manipulated local manifest can inject administrator HTML. Installed extension files are already a privileged trust boundary, so severity is low.
- Recommendation: validate folder/element identifiers, handle `false`, and escape version/description before HTML output.

### LOW-05 - Transport exception contract is inconsistent

- Evidence: public REST methods call Joomla HTTP methods before `decodeResponse()` and do not wrap factory/transport exceptions. Only decoded HTTP/business errors become `TransportException`.
- Impact: consumers cannot reliably catch one library exception type for network failures; administrator fields compensate with broad `\Exception` catches.
- Recommendation: wrap Joomla transport exceptions consistently, preserve the previous exception, and avoid exposing low-level implementation types.

## Joomla API Verification

### Existing and valid in Joomla 5.4.5 and 6.1.0

- `Factory::getApplication()` and `Factory::getContainer()` exist. They are service-locator usage and weaker than constructor injection, but are not invented calls.
- `InstallerAdapter::getManifest()` exists at `libraries/src/Installer/InstallerAdapter.php:570`.
- `InstallerScriptInterface` lifecycle signatures used by `script.php` match Joomla 5/6.
- `PluginHelper::getPlugin()` and `PluginHelper::isEnabled()` exist.
- `CMSPlugin::setApplication()`, `DatabaseDriver::updateObject()`, and `HTMLHelper::_('select.option', ...)` exist.
- `WebAssetManager::addInlineStyle()` is a documented dynamic method handled by `WebAssetManager::__call()` and is used by Joomla core itself.
- `Application::getDocument()` and `Document::getWebAssetManager()` are valid for the administrator application.

### Deprecated

- `Joomla\CMS\Http\HttpFactory` / `getHttp()` only. The replacement is `Joomla\Http\HttpFactory`.

### Not found

- No invented Joomla API methods or classes were found in the product code.
- No removed Joomla 3-only classes, raw `JLoader` namespace registration, legacy modal APIs, or direct superglobal access were found.

## Architecture And Overengineering

- The facade + thin REST transport + separate SOAP tracking + selected entity layer is proportionate to the integration domain. No repository/service/value-object hierarchy or unnecessary framework was found.
- The entity layer performs real key normalization, nested hydration, defaults, and limited required-field validation; it is not empty abstraction.
- `Factory` service-locator calls in the plugin provider/installer are avoidable architectural debt. Injecting application/database/factories directly would make dependencies explicit and improve tests.
- Plugin configuration is duplicated in the manifest and `plg_system_wt_otpravkapochtaru/config/config.xml`; the shipped copy is redundant and can drift.
- The empty system plugin is justified as Joomla's configuration surface for a library, so its existence is not overengineering.

## Security Positives

- No raw `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE`, or `$_FILES` access.
- No `eval`, `unserialize`, shell execution, disabled TLS verification, or hard-coded credential literals.
- REST base hosts are fixed HTTPS constants, limiting SSRF exposure.
- Account API values rendered in `AccountinfoField` are escaped.
- External links using `target="_blank"` include `rel="noopener noreferrer"`.
- Installer/database values are fixed package identifiers rather than user input.

## Performance Observations

- The dominant risk is remote I/O in FormField rendering, not local array/entity processing.
- Recursive payload normalization is linear in payload size and acceptable for ordinary shipment batches.
- `array_merge()` in tracking failure accumulation is minor compared with network I/O, though direct append would avoid repeated copies for very large lists.
- `CountryDictionary` is static local data and does not introduce remote latency.

## Executed Verification

- PHP lint: passed for all product and test PHP files.
- PHPUnit: passed, 3 tests / 4 assertions.
- PHPStan: passed at configured level 1 against Joomla 6.1 sources.
- PHPCS: passed.
- `composer.json`: valid JSON, PHP platform `8.1.0`, `ext-simplexml` and `ext-soap` declared.
- Composer CLI validation was not available because the local `composer.bat` wrapper resolves a missing `\composer.phar`; this is a tooling limitation, not a package finding.

## Priority Order

1. Replace deprecated CMS `HttpFactory` and introduce a reusable/injectable client.
2. Fix filename sanitization before consumers persist binary documents.
3. Remove synchronous API calls from normal administrator form rendering and cap timeouts.
4. Add transport/credentials/tracking/security tests and Joomla 5/6 compatibility gates.
5. Mask credential fields and disable SOAP trace by default.

## Residual Boundaries

- No live hostile-input penetration test was performed.
- Russian Post endpoint correctness was not re-audited; this task targeted Joomla API and local backend/security architecture.
- No product code was changed.

## 2026-07-11 Remediation Status

- `MEDIUM-01` fixed locally: `Request` now uses `Joomla\Http\HttpFactory`, verified against Joomla 5.4.5 and 6.1.0 framework sources.
- `MEDIUM-02` fixed locally: download filenames are reduced to a basename, forbidden/control characters are replaced, trailing dots/spaces are removed, and Windows reserved device names fall back to `document`.
- Findings `MEDIUM-03` through `LOW-05` were not changed.
- Updated package: `.packages/WT Otpravkapochtaru_3.0.0.zip`, SHA-256 `C68F71A6F4D6B7E207CDF8684C11BFA457D7A1934C2D90BACC5DD1A767E22C1A`.
- Test coverage proposal: `.webtolk/docs/reports/test-coverage-proposal-20260711.md`.
