# Thin wrapper migration plan review

status: needs-more-proof
run_id: lapaygroup-thin-wrapper-migration-20260811
task_id: t24-plan-review
requested_mode: planning review
artifact_scope:
  - .pf/artifacts/worker-thin-wrapper-current-inventory-20260811.md
  - .pf/artifacts/worker-thin-wrapper-lapaygroup-inventory-20260811.md
  - .pf/artifacts/worker-thin-wrapper-plugin-settings-contract-20260811.md
  - .pf/artifacts/worker-thin-wrapper-joomla-fields-contract-20260811.md
  - .pf/artifacts/worker-thin-wrapper-package-strategy-20260811.md
  - .pf/artifacts/worker-thin-wrapper-test-gates-20260811.md

## findings_by_severity

### high
- [BLOCKER] Field webasset is still tied to the plugin package namespace.
  - Evidence: `Required asset name: plg_system_wtotpravkapochtaru.linked-select-fields` and `registerAndUseScript(name, 'plg_system_wtotpravkapochtaru/linked-select-fields.js', ...)`.
  - Problem: this violates the boundary check requirement that the field package include its own asset and avoid plugin-level hard bind.

- [BLOCKER] Package version source of truth is not resolved.
  - Evidence: package strategy says to derive package version and date from composer lock entries for `lapaygroup/russianpost`, while plugin contract requires migration path from package version `2.0.1` to `3.0.0`.
  - Problem: this can cause release and compatibility confusion unless there is an explicit policy for independent extension version + separate SDK metadata.

### medium
- [BLOCKER] Dependency blocker is not fully proven in reviewed artifacts.
  - SDK PHP requirement is `^8.3`; reviewed artifacts do not provide current manifest `manifest.xml` or `pkg_lib_wt_otpravkapochtaru.xml` minimum PHP values.
  - This is required as a hard compatibility precondition by test gates.

- [BLOCKER] Tracking path risk remains unresolved.
  - Current library inventory marks SOAP based tracking code for decision, while LapayGroup inventory identifies SOAP based tracking provider and ext-soap requirement.
  - Without an explicit separation strategy, runtime tracking can become inconsistent across thin wrapper and legacy behavior.

### low
- Artifact quality issues:
  - Several reviewed files contain non ascii symbols and Cyrillic text (for example arrows and Russian strings in tables and headings).
  - Output artifacts should be cleaned to strict ascii/plain text and normalized line encoding before they are treated as release evidence.

## acceptance_checklist
- JoomShopping coupling: not detected in current artifacts.
- Old public facade compatibility: explicitly rejected in contract, no worker requires it.
- Plugin settings compatibility: explicitly required with legacy+canonical read/write matrix and upgrade tests.
- Webasset boundary: fails current check and requires correction.
- PHP requirement check: requires external confirmation of package declared requirement vs SDK `^8.3`.
- Versioning strategy: requires explicit policy decision (independent extension version vs SDK lock version inheritance).

## implementation_sequence
1. Decide version governance first:
   - Define whether extension version follows release train (`2.0.1 -> 3.0.0`) and store SDK version/date separately in metadata.
   - Update plan to remove ambiguous "version from lockfile" instruction or formalize override rules.
2. Add compatibility gate checks for minimum PHP:
   - Confirm and enforce `^8.3` in package manifests and installer guard logic.
   - Validate ext-soap and ext-mbstring requirements during preflight as called out in test gates.
3. Fix field asset ownership:
   - Move linked select JS and registration to a generic library field package asset namespace.
   - Keep plugin manifest fields consuming the field package contract without hard-coded plugin namespace paths.
4. Finalize plugin settings contract implementation:
   - Implement compatibility matrix for legacy keys and canonical keys exactly as contract.
   - Keep `CredentialsProvider` behavior for migration and upgrade paths.
5. Implement thin wrapper classes and removals:
   - Replace/remove transport/entity/legacy layers per current inventory decisions.
   - Keep plugin-facing exception wrappers and compatibility entry points only.
6. Implement SDK packaging flow:
   - composer update in release workflow, lockfile-based SDK discovery, package-local autoloader runtime bundle.
   - Keep separate installer cleanup/validation and release zip inspection list.
7. Close remaining test gates:
   - Gate 1 unit additions.
   - Gate 2 upgrade and settings preservation.
   - Gate 3 classloader and package inspection.
   - Gate 4 generic field rendering.
   - Gate 5 read-only smoke, with network blocker documented.

## likely_writer_file_ownership
- Writer 1 (compatibility):
  - lib_webtolk_otpravkapochtaru/src/Configuration/CredentialsProvider.php
  - tests/Unit/Configuration/CredentialsProviderTest.php
  - tests/Unit/Configuration/CredentialsProviderCompatibilityTest.php (new/extend)
- Writer 2 (field layer):
  - lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php
  - lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php
  - plg_system_wt_otpravkapochtaru/media/linked-select-fields.js
  - plg_system_wt_otpravkapochtaru/media/joomla.asset.json
  - tests/Unit/Fields/LinkedSelectFieldTest.php
  - tests/Unit/Fields/PluginFieldRenderSmokeTest.php
- Writer 3 (wrapper core):
  - lib_webtolk_otpravkapochtaru/src/Webtolk/Otpravkapochtaru.php
  - lib_webtolk_otpravkapochtaru/src/Service/LinkedSelectOptionsService.php
  - lib_webtolk_otpravkapochtaru/src/Fields/{OpslistField.php,MailtypesField.php,MailcategoriesField.php,AccountinfoField.php}
  - lib_webtolk_otpravkapochtaru/src/Exception/{ConfigurationException.php,TransportException.php,OtpravkapochtaruException.php}
  - tests/Unit/...
- Writer 4 (release and runtime packaging):
  - composer.json
  - .github/workflows/release.yml
  - build/release.php
  - .dist/build/package.config.json
  - script.php
  - lib_webtolk_otpravkapochtaru/src/libraries/autoload.php
  - lib_webtolk_otpravkapochtaru/src/libraries/lapaygroup/russianpost/**
  - lib_webtolk_otpravkapochtaru/otpravkapochtaru.xml
  - pkg_lib_wt_otpravkapochtaru.xml
  - .pf/artifacts/joomla-local-*.md for regression evidence

## remaining_human_decisions
- Confirm strict policy for package version and SDK metadata version coupling.
- Confirm that field webasset must be fully generic and library-owned before writer 2 starts.
- Confirm minimum PHP/Joomla versions currently enforced by `script.php` and manifest.
- Decide whether tracking SOAP path is required now, and if so separate tracking adapter handling is planned.
