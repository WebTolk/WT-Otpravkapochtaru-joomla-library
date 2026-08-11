# Joomla System Requirements Check: PHP 8.3 and mbstring

Date: 2026-08-11

## Scope

Verify package system requirements against local Process Forge Joomla documentation before raising WT Otpravkapochtaru package requirements.

## Local Sources Checked

- `D:/.agents/docs/joomla/Joomla-context7/2026-02-21-refresh/manual_joomla__overview.md`
- `D:/.agents/docs/joomla/core/Joomla-core/6.x/6.1.2/administrator/index.php`
- `D:/.agents/docs/joomla/core/Joomla-core/6.x/6.1.2/index.php`
- `D:/.agents/docs/joomla/core/Joomla-core/6.x/6.1.2/installation/src/Model/ChecksModel.php`
- `D:/.agents/docs/joomla/core/Joomla-core/6.x/6.1.2/libraries/vendor/composer/platform_check.php`
- `D:/.agents/docs/joomla/core/Joomla-core/5.x/5.4.5/administrator/index.php`
- `D:/.agents/docs/joomla/core/Joomla-core/5.x/5.4.5/libraries/vendor/composer/platform_check.php`
- `D:/.agents/docs/joomla/core/Joomla-6-1-docs/core-api-overview-docs-articles/cms/installer/kak-rabotaet-script-php-rasshireniya-joomla.md`

## Findings

- The local Context7 snapshot of `manual.joomla.org` contains the manual overview and extension-development topics, but not a full system-requirements table.
- Local Joomla 6.1.2 core defines `JOOMLA_MINIMUM_PHP` as `8.3.0`.
- Local Joomla 6.1.2 Composer platform check requires PHP `>= 8.3.0`.
- Local Joomla 5.4.5 core defines `JOOMLA_MINIMUM_PHP` as `8.1.0`.
- Joomla installer checks include `zlib`, `xml`, database support, and `mbstring` configuration when `mbstring` is loaded.
- Joomla core/update checks do not make SOAP a Joomla system requirement.

## Decision

- WT Otpravkapochtaru 3.0.0 raises its package PHP requirement to `>=8.3.0`.
- `ext-mbstring` is required by the package metadata and installer preflight.
- `ext-simplexml` remains required by package metadata.
- `ext-soap` is not a package-level requirement. SOAP is optional and only needed for tracking methods.

## Product Changes

- `composer.json`: require `php >=8.3.0`, `ext-mbstring`, `ext-simplexml`; remove `ext-soap`.
- `script.php`: installer preflight now checks PHP `8.3.0` and required PHP extensions.
- `language/en-GB/pkg_lib_wt_otpravkapochtaru.sys.ini`: added missing-extension installer message.
- `language/ru-RU/pkg_lib_wt_otpravkapochtaru.sys.ini`: added missing-extension installer message.
- `README.md`: requirements updated to PHP 8.3.0+, mbstring required, SOAP optional for tracking.

## Verification

- `php -l D:\Dev\WT-Otpravkapochtaru-joomla-library\script.php`: passed.
- `composer.json` JSON parsing through PowerShell `ConvertFrom-Json`: passed.
- `lib_webtolk_otpravkapochtaru/media/joomla.asset.json` JSON parsing through PowerShell `ConvertFrom-Json`: passed.
- `git diff --check` for touched files: passed.
- `phing -f D:\Dev\WT-Otpravkapochtaru-joomla-library\phing.xml "3. Package release"`: passed.
- Built archive: `.packages/WT Otpravkapochtaru_3.0.0.zip`, 41 entries, 62097 bytes.

## Verification Gap

`composer validate --no-check-publish` could not run in the local CLI environment because Composer requires the PHP `openssl` extension for TLS initialization and this CLI PHP does not load `openssl`. This failure is environmental and happened before Composer evaluated the project metadata.

## 2026-08-11 Correction: Composer SOAP Requirement And Joomla Installer Warning

The SOAP policy was narrowed after release-readiness review and user clarification:

- `ext-soap` is required in `composer.json` for Composer/GitHub build workflows.
- Joomla package installation must not block when PHP SOAP is missing.
- Joomla installer post-install/post-update output must warn that tracking methods will not work until SOAP is enabled.
- Required Joomla installer preflight extensions remain limited to runtime-critical non-tracking requirements; currently `mbstring`.

Product changes for this correction:

- `composer.json`: restored `ext-soap`.
- `script.php`: added a non-blocking SOAP warning to the existing branded installer message for install/discover-install/update flows.
- `language/en-GB/pkg_lib_wt_otpravkapochtaru.sys.ini`: added localized optional SOAP warning text.
- `language/ru-RU/pkg_lib_wt_otpravkapochtaru.sys.ini`: added localized optional SOAP warning text.
- `README.md`: documented that `ext-soap` is declared for Composer/GitHub builds, while ready Joomla ZIP installation only warns when SOAP is absent.

Verification after correction:

- `php -l D:\Dev\WT-Otpravkapochtaru-joomla-library\script.php`: passed.
- `composer.json` JSON parsing through PowerShell `ConvertFrom-Json`: passed; `ext-soap` is present in `require`.
- `git diff --check` for the corrected files: passed.
- `composer validate --no-check-publish D:\Dev\WT-Otpravkapochtaru-joomla-library\composer.json`: blocked by local CLI PHP missing `openssl`, before Composer evaluated project metadata.
- `phing -f D:\Dev\WT-Otpravkapochtaru-joomla-library\phing.xml "3. Package release"`: passed.
- Built archive: `.packages/WT Otpravkapochtaru_3.0.0.zip`, 41 entries, 62326 bytes.
- Archive inspection confirmed the updated `script.php` warning hook and both localized warning keys are present in the ZIP.
- Joomla local install smoke with OSPanel PHP 8.3 and SOAP deliberately omitted from CLI extensions:
  `extension:install --path=".packages/WT Otpravkapochtaru_3.0.0.zip"` passed on `D:\OSPanel\home\joomla.local\public`.
- Installer message probe `.pf/tmp/installer_soap_warning_probe.php` confirmed:
  without SOAP `warning_present=yes`; with normal OSPanel PHP where SOAP is loaded `warning_present=no`.
