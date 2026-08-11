# Installer SOAP Warning Audit

- run_id: `soap-policy-worker-audit-20260811`
- task_id: `t31-installer-soap-warning`
- mode: read-only product audit

Verdict: **pass**

## Evidence
- `script.php:80` defines `requiredPhpExtensions` as `['mbstring']` only.
- `script.php:147-173` and related preflight flow uses the required extension check list from `requiredPhpExtensions`, with no `soap` entry added there.
- `script.php:212` explicitly checks `!extension_loaded('soap')` inside `renderInstallationMessage()` path.
- `script.php:208-213` renders `PKG_LIB_WT_OTPRAVKAPOCHTARU_WARNING_OPTIONAL_SOAP_MISSING` only inside the same render path.
- `script.php:212` gate condition `if ($type !== 'uninstall')` keeps the SOAP warning from being emitted on uninstall.
- `language/en-GB/pkg_lib_wt_otpravkapochtaru.sys.ini:8` contains `PKG_LIB_WT_OTPRAVKAPOCHTARU_WARNING_OPTIONAL_SOAP_MISSING`.
- `language/ru-RU/pkg_lib_wt_otpravkapochtaru.sys.ini:8` contains `PKG_LIB_WT_OTPRAVKAPOCHTARU_WARNING_OPTIONAL_SOAP_MISSING`.

## Residual risk
- Low: warning message text quality is not part of this check; only presence and path wiring were verified in code.
- No code was edited.
