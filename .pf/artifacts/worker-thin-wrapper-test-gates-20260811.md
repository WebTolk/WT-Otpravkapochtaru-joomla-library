# Worker Artifact: t23-test-gates
run_id: lapaygroup-thin-wrapper-migration-20260811
run_mode: shell-worker (read-only planning)
assigned_task: t23-test-gates
output_path: .pf/artifacts/worker-thin-wrapper-test-gates-20260811.md

## Scope
- Thin-wrapper migration planning for `lapaygroup/russianpost` 2.0.0.
- Must keep plugin parameter compatibility and generic Joomla field behavior.
- No JoomShopping-specific library behavior is in scope.
- No product code or stand filesystem writes in this run.

## Gate Order and Responsibility

| Gate | Focus | Decision | Minimum evidence location |
| --- | --- | --- | --- |
| 1 | Unit coverage snapshot / additions | Must pass before local-update gates | `tests/Unit/**`, `.pf/artifacts/...` |
| 2 | Joomla local update safety | Must pass before release-package gates | `.pf/artifacts/joomla-local-*-test-20260810.md` |
| 3 | Classloader + package inspection | Must pass before release ZIP install checks | `.pf/artifacts/worker-lapaygroup-core-swap-writer-20260811.md`, package inspection artifacts |
| 4 | Generic Joomla Form field rendering | Must pass before runtime smoke and release | dedicated render checks on field classes |
| 5 | Read-only API smoke | Must pass after Gates 1-4 | `.pf/artifacts/worker-lapaygroup-runtime-smoke-20260811.md` + new smoke probe |
| 6 | Release blockers + acceptance | Final release go/no-go | `phing.xml`, `.dist/build/package.config.json`, package zip evidence |

## 1. Unit tests

### Existing required baseline (preserve as-is)
- `tests/Unit/Configuration/CredentialsProviderTest.php` (3 tests):
  - canonical key read
  - legacy plugin key mapping (`AccessToken`, `user_key_or_login_and_password`, `user_auth_key`)
  - canonical precedence over legacy keys
- `tests/Unit/Fields/LinkedSelectOptionsServiceTest.php` (4 tests):
  - shipping point selection
  - mail-type primary/payload fallback
  - category sort/filter behavior
- `tests/Unit/Entity/OrderTest.php` (2 tests):
  - normalization defaults
  - destination index validation
- `tests/Unit/Dictionaries/CountryDictionaryTest.php` (1 test)

### Additional minimal unit additions for thin wrapper migration
1. **`tests/Unit/Fields/LinkedSelectFieldTest.php`**
   - Instantiate each field class with synthetic `SimpleXMLElement` form field config.
   - Assertions:
     - `OpslistField::getInput()` returns a Joomla `<select>` and includes `name="...linked_test_shipping_point"`.
     - `MailtypesField::getInput()` and `MailcategoriesField::getInput()` include request metadata attributes (`data-wt-watchfield`, `data-wt-parentfield` as configured).
     - `getOptions()` is deterministic under missing upstream data (empty array fallback).

2. **`tests/Unit/Configuration/CredentialsProviderCompatibilityTest.php`** (or extend existing test file)
   - Add cases for the canonical + legacy parameter map used on installed plugin rows.
   - Assert no exception and stable return types for `getAccessToken`, `getAuthMode`, `getUserKey`, `getTrackingLogin`, `getTrackingPassword`.

3. **`tests/Unit/Fields/PluginFieldRenderSmokeTest.php`**
   - Render synthetic form XML that includes all generic field types (`opslist`, `mailtypes`, `mailcategories`, `accountinfo`) without JoomShopping context.
   - Verify HTML output can be produced and contains expected `form-control`+request wiring attrs only.

**Gate 1 pass conditions**
- `phpunit -c phpunit.xml --testsuite Unit` returns `OK` (all current + added tests).
- `CredentialsProvider` and `LinkedSelectOptionsService` behaviors stay unchanged in semantics.

## 2. Joomla local update tests (non-JoomShopping)

### Reuse historical baseline
- `joomla-local-201-to-300-plugin-params-upgrade-test-20260810.md` proves plugin params survive 2.0.1 -> 3.0.0 update.
- `joomla-local-201-to-300-legacy-library-cleanup-test-20260810.md` proves legacy `Webtolk/Pochtaru` removal via installer lifecycle.

### Thin-wrapper update acceptance tests to define
1. **Plugin settings preservation on thin-wrapper package upgrade**
   - Install baseline package (current latest production candidate).
   - Seed system plugin `wtotpravkapochtaru` params using legacy keys and deterministic secrets.
   - Apply thin-wrapper package upgrade on `joomla.local` stand.
   - Checks:
     - same extension row exists (or known expected update behavior)
     - `params` JSON contains legacy keys and values unchanged
     - legacy key names are still shown by the plugin form
     - `CredentialsProvider` reads legacy values correctly.

2. **No consumer-specific coupling verification**
   - Ensure no JoomShopping-only assertions are made in this update test.
   - Scope only `wtotpravkapochtaru` plugin row + `lib_webtolk_otpravkapochtaru` extension update path.

3. **Autonomous installability check**
   - Remove and reinstall package on clean stand state.
   - Validate plugin row remains enabled, package install state is consistent, and no fatal errors in Joomla error logs for admin load path.

**Gate 2 pass conditions**
- PASS `joomla-local` install/enable checks.
- PASS plugin params round-trip for both legacy and canonical keys.
- PASS no regression markers from old migration tests.

## 3. Classloader and package inspection tests

1. **Classloader proof on stand/runtime (stand-inside-Joomla path)**
   - Reuse `autoload-proof.php`-style check:
     - `class_exists('LapayGroup\\RussianPost\\Http\\Psr18Transport')`
     - instantiate `LapayGroup\RussianPost\Http\Psr18Transport` with `Joomla\Http\Http`, `Laminas\Diactoros\RequestFactory`, `StreamFactory`, `UploadedFileFactory`.
   - Confirm no manual `addPsr4()` runtime is required in final package; loading is via package-local autoload.

2. **Package inspection checklist for release ZIP (smallest complete set)**
   - Package contains:
     - library + plugin folders (`lib_webtolk_otpravkapochtaru`, `plg_system_wtotpravkapochtaru`)
     - SDK runtime path used by wrapper
     - package-local autoload file for `LapayGroup\RussianPost\`
     - `pkg_lib_wt_otpravkapochtaru.xml`, `script.php`, plugin manifest and JS/CSS assets
   - Package does **not** contain:
     - `.github`
     - stale temp/vendor trees outside allowed release assets
     - legacy `Webtolk/Pochtaru` source.
   - Record file count/size/hash and diff-free manifest checks.

3. **phing packaging check (existing tooling shape)**
   - `phing -f phing.xml "3. Package release"` must produce a deterministic zip and include packaging metadata replacement.

**Gate 3 pass conditions**
- Class autoload and constructor compatibility are proven.
- Package contains required SDK wrapper/runtime paths and no forbidden entries.
- Packaging command succeeds in read-only allowed environment.

## 4. Generic Joomla field rendering tests

### Minimal test matrix
1. **Field type output test**
   - Build a synthetic admin form XML using the library field types (`opslist`, `mailtypes`, `mailcategories`, `accountinfo`).
   - Render form via Joomla form API in unit context.
   - Assert no exceptions and valid HTML.

2. **Request-fields chain contract**
   - Validate `mailtypes` has request metadata for `postoffice_code` and `mailcategories` has parent linkage to `mail_type`.
   - Validate fallback options are valid arrays when API data unavailable.

3. **Asset registration path smoke**
   - Verify field classes register required assets through media package path and not through hardcoded legacy aliases.
   - Assert `public/media/plg_system_wtotpravkapochtaru/js/linked-select-fields.js` and corresponding `joomla.asset.json` are present in package/stand layout.

4. **Locale rendering sanity**
   - Instantiate fields with English and Russian locales.
   - Ensure labels render through Joomla language and no fatal translation path breaks.

**Gate 4 pass conditions**
- All generic field types are renderable and carry request wiring.
- No JoomShopping-specific field definitions are required for these assertions.

## 5. Read-only API smoke tests

Run in local stand context with stored credentials loaded from plugin params; do **not** mutate orders or remote account state.

1. `OtpravkaApi::settings()`
2. `OtpravkaApi::shippingPoints()`
3. `searchPostOfficeByIndex()` with synthetic postal index (e.g. `101000`)
4. `Calculation::getTariff()` or equivalent tariff read path with deterministic fixture-like payload shape

### Assertions
- transport init succeeds with Joomla HTTP client and Laminas factories.
- request signatures and response shapes are non-empty and parseable.
- response counts/types are stable (`settings keys`, `shipping point count`, `postoffice results`, tariff keys).
- no secrets or headers are printed in artifacts (only booleans, counts, redacted lengths).
- failures are classified: dependency, credential, transport, or API endpoint mismatch.

**Existing status note**
- Prior smoke run shows all four real calls failed with outbound network blocker to `127.0.0.1:443` on environment.
- This is a known `Gate 5` blocker until network path is available or fixture/mocked endpoint is used for release precheck.

## Release Blockers (hard stop)

1. **Network/runtime blockers**
   - No outbound HTTPS to api endpoints in local test environment.

2. **Dependency blockers**
   - PHP runtime must satisfy SDK requirement (`^8.3`) and required extensions (`ext-soap`, `ext-mbstring`).

3. **Package blockers**
   - Missing package-local SDK autoload wiring.
   - Packaging path leaks forbidden content (`.github`, runtime temp paths, stale legacy library artifacts).

4. **Compatibility blockers**
   - Plugin parameter migration path does not preserve legacy keys.
   - Generic field request metadata (`requestfields`, watchfield linkage) missing after refactor.

5. **Security blockers**
   - Any token/user key logged in artifacts.
   - Any mutation call in API smoke path (orders/create/edit/delete batch ops).

## Explicit acceptance criteria

Release is approved only if all gates pass in order:
1. Gate 1: full unit suite passes with added thin-wrapper field/unit checks.
2. Gate 2: plugin update preserves params across package install/upgrade and legacy settings remain readable.
3. Gate 3: classloader proof passes and release package passes inspection (exact SDK namespace + autoload, no forbidden entries).
4. Gate 4: generic Joomla field rendering works in isolation (not JoomShopping-bound).
5. Gate 5: read-only API smoke passes for settings/shipping-points/postoffice/tariff with no credential leakage and classified failures.

Additional acceptance invariants:
- Library remains consumer-neutral and does not re-introduce JoomShopping-only behavior.
- No account-mutating calls are executed in smoke.
- Installer/update path preserves plugin row continuity and plugin param compatibility.
