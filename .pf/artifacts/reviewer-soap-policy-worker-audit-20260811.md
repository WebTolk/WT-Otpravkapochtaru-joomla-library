# Reviewer: t33-soap-policy-review
run_id: soap-policy-worker-audit-20260811
task_id: t33-soap-policy-review
mode: shell-worker-reviewer
model: gpt-5.3-codex-spark
verdict: pass

accepted_findings:
- composer_vs_installer_policy_split_confirmed
  - Evidence: composer.json requires `php >= 8.3.0`, `ext-mbstring`, `ext-simplexml`, and `ext-soap`.
  - Evidence: script.php preflight required extension list is `['mbstring']` (`$requiredPhpExtensions`), while SOAP is checked as a separate optional warning in `renderInstallationMessage()`.
- no_blocking_installer_for_missing_soap_confirmed
  - Evidence: preflight requires extensions only through `checkRequiredPhpExtensions()` and does not include `soap`.
  - Evidence: `requiredPhpExtensions` is exactly `['mbstring']` in script.php.
- optional_warning_shown_for_non_uninstall_flows_confirmed
  - Evidence: `renderInstallationMessage()` adds optional warning when `!extension_loaded('soap')` and `$type !== 'uninstall'`.
  - Evidence: warning key exists in both language files: `PKG_LIB_WT_OTPRAVKAPOCHTARU_WARNING_OPTIONAL_SOAP_MISSING` in `language/en-GB/pkg_lib_wt_otpravkapochtaru.sys.ini` and `language/ru-RU/pkg_lib_wt_otpravkapochtaru.sys.ini`.
- runtime_smoke_verification_confirms_install_and_warning_behavior
  - Evidence: smoke report confirms `[OK] Extension installed successfully` with SOAP omitted and `warning_present=yes`.
  - Evidence: smoke report confirms same install succeeds with normal PHP and `warning_present=no`.
- no_product_code_change_request_left_by_workers
  - Evidence: all three worker artifacts are final audit verifications and report `pass` with no pending code-change directives.

rejected_findings:
- no_conflicting_findings_detected
  - T31 and T32 are consistent in scope: ext-soap is optional and only warning-based.
- no_evidence_of_policy_mixup
  - No report claims blocking installer behavior on missing SOAP in a way not contradicted by source.

residual_risks:
- Warning behavior is validated in script-level and CLI smoke paths; not yet validated by full Joomla backend GUI install UI rendering.
- No automated guard currently enforces that `soap` remains out of `$requiredPhpExtensions` in future edits.
- Local smoke job installed into `joomla.local` twice, so environment state change is possible if re-run assumptions require a clean stand.
