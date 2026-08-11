# Reviewer Artifact: t17-lapaygroup-core-swap
run_id: `lapaygroup-joomla-core-swap-20260811`
task_id: `t17-lapaygroup-core-swap-review`
mode: evidence review
requested_model: `gpt-5.3-codex-spark`
completed_at: `2026-08-11`

## Migration proof decision

`needs-more-proof`

Direct substitution of `lapaygroup/russianpost` 2.0.0 as a product migration basis is **not proven enough** yet.

## Required evidence checks

### 1) classloader/constructor proof
- `PASS` — `LapayGroup\\RussianPost\\Http\\Psr18Transport` is present in the sourced SDK (`src`) and registered in stand composer autoloader.
  - Proof: writer artifact shows added PSR-4 map in `libraries/vendor/composer/autoload_psr4.php` and matching `autoload_static.php` entry.
  - Proof: `autoload-proof.php` in T14 reports `class_exists=yes` and instantiation checks for interface wiring.
- `PASS` — dependency side requirements are available in stand runtime (`Joomla\Http\Http`, Laminas Diactoros factories, PSR-HTTP interfaces).
  - Proof: T07 and T12 dependency probes.

### 2) credentials proof
- `PASS` — plugin credentials are sourced and queryable from Joomla DB in read-only mode.
  - Proof: T13 and T15 show non-empty `AccessToken`, `user_login`, `user_password`, `user_auth_key`, and `user_key_or_login_and_password` lengths.
- `INFO` — auth mode resolved in smoke as `key`, but `access_token_present` only; tracking/missing optional keys are explicitly listed and remain unverified on live response flow.

### 3) live API proof
- `NEEDS-MORE-PROOF` — environment blocks outbound HTTPS to `otpravka-api.pochta.ru` and `delivery.pochta.ru` (`127.0.0.1:443` connection errors).
  - T15/T09 runtime calls for settings/shippingPoints/postoffice lookup/tariff all fail on transport-level connectivity.
- This means no validated read-only upstream API response shape, endpoint compatibility, or auth header correctness can be proven in this run.

### 4) JoomShopping form proof
- `PARTIAL PASS` — install/enable and form metadata checks are present.
  - `PASS` for plugin enablement and form XML parse via CLI.
  - `PASS` for webasset file presence (`joomla.asset.json`, JS).
- `BLOCKED` for browser-level form rendering and end-to-end admin-page verification (no local HTTP endpoint in this session).

### 5) package/runtime parity risk
- `HIGH RISK / NEEDS-MORE-PROOF`
- T10 matrix identifies adapter-dependent areas with non-trivial behavior-risk:
  - entity normalization, defaults/filtering, binary document handling,
  - order/edit/delete/batch semantics,
  - tracking/shipping field derivation paths,
  - SOAP/tracking path compatibility,
  - and additional public methods in product facade not fully mapped.
- Current matrix notes partial inference and missing runtime verification due SDK install/probe limitations.

## Additional required checks

- No product repo files modified:
  - `PASS` (T14 explicitly states no product repository source edits; changes are limited to stand runtime and temporary vendor/autoloader files).
- No raw secrets written to artifacts:
  - `PASS` (artifacts contain only parameter labels and lengths; no raw credentials/token values detected).
- T14 restore instructions:
  - `PASS` — explicit backup restore commands for `autoload_psr4.php`, `autoload_static.php`, and SDK directory cleanup are present.

## Reviewer verdict

Given unresolved live API proof and partial parity validation, this is not safe to propose for migration now.

Final status: `needs-more-proof`
