# SOAP Policy Package & Local Smoke Audit

verdict: pass

commands_run:
- `Test-Path '.packages/WT Otpravkapochtaru_3.0.0.zip'`
- `Get-Content '.pf/runtime/agent-runs/soap-policy-worker-audit-20260811/t32-package-local-smoke/workspace-access.json'`
- `Get-Content -Raw '.pf/tmp/installer_soap_warning_probe.php'`
- `Get-Content -Path 'D:/OSPanel/modules/PHP-8.3/php.ini'` and `Select-String` for SOAP entry
- `D:/OSPanel/modules/PHP-8.3/php.exe D:/OSPanel/home/joomla.local/public/cli/joomla.php extension:install --help`
- `New-Item`/`Set-Content` temporary `php-no-soap.ini` in `.pf/runtime` (commented `extension = soap`), then:
  - `D:/OSPanel/modules/PHP-8.3/php.exe -c .pf/runtime/php-no-soap.ini -r "extension_loaded('soap')"`
  - `D:/OSPanel/modules/PHP-8.3/php.exe -c .pf/runtime/php-no-soap.ini D:/OSPanel/home/joomla.local/public/cli/joomla.php extension:install --path="D:/Dev/WT-Otpravkapochtaru-joomla-library/.packages/WT Otpravkapochtaru_3.0.0.zip" --no-interaction`
  - `D:/OSPanel/modules/PHP-8.3/php.exe D:/OSPanel/home/joomla.local/public/cli/joomla.php extension:install --path="D:/Dev/WT-Otpravkapochtaru-joomla-library/.packages/WT Otpravkapochtaru_3.0.0.zip" --no-interaction`
  - `D:/OSPanel/modules/PHP-8.3/php.exe .pf/tmp/installer_soap_warning_probe.php`
  - `D:/OSPanel/modules/PHP-8.3/php.exe -c .pf/runtime/php-no-soap.ini .pf/tmp/installer_soap_warning_probe.php`
- `Read` ZIP entries from `.packages/WT Otpravkapochtaru_3.0.0.zip` using .NET `System.IO.Compression.ZipFile`

evidence:
- ZIP is present at the requested path.
- ZIP contents include `script.php` (entry count 41 total):
  - `script.php`
- `script.php` inside ZIP contains SOAP policy logic:
  - contains `optionalSoapWarning` token
  - contains `extension_loaded('soap')`
  - render logic includes `PKG_LIB_WT_OTPRAVKAPOCHTARU_WARNING_OPTIONAL_SOAP_MISSING` in warning block
- ZIP contains localized package/system language files:
  - `language/en-GB/pkg_lib_wt_otpravkapochtaru.sys.ini`
  - `language/ru-RU/pkg_lib_wt_otpravkapochtaru.sys.ini`
  - `plg_system_wt_otpravkapochtaru/language/en-GB/plg_system_wtotpravkapochtaru.ini`
  - `plg_system_wt_otpravkapochtaru/language/en-GB/plg_system_wtotpravkapochtaru.sys.ini`
  - `plg_system_wt_otpravkapochtaru/language/ru-RU/plg_system_wtotpravkapochtaru.ini`
  - `plg_system_wt_otpravkapochtaru/language/ru-RU/plg_system_wtotpravkapochtaru.sys.ini`
- ZIP contains library-owned web assets:
  - `lib_webtolk_otpravkapochtaru/media/joomla.asset.json`
  - `lib_webtolk_otpravkapochtaru/media/js/linked-select-fields.js`
- Runtime SOAP-omitted check succeeded:
  - temporary no-soap config reports `soap_loaded=no`
  - CLI installer with no-soap PHP run returned `[OK] Extension installed successfully.` and exit code `0`
- Default PHP probe/install check succeeded:
  - probe output `soap_loaded=yes` and `warning_present=no`
  - probe run under no-soap config output `soap_loaded=no` and `warning_present=yes`
  - CLI install with default PHP returned `[OK] Extension installed successfully.` and exit code `0`

residual_risk:
- Probe reads `renderInstallationMessage` directly and validates string presence, not a full end-to-end browser/administrator UI rendering in Joomla backend.
- The no-soap runtime was tested using a temporary copied `.ini`; if another environment-level PHP init path is used in future automation, SOAP status may differ.
- Installer was executed twice on `joomla.local` during this smoke job; this can affect stand state if later checks assume pre-run state.
