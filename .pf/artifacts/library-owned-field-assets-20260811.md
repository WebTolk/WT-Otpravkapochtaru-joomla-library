# Library-Owned Joomla Field Assets

Task date: 2026-08-11
Product code changed: yes

## Decision

Linked-select Joomla Form assets now belong to the library package, not to the
system plugin.

Runtime install target:

- JS: `JPATH_SITE/media/lib_wt_otpravkapochtaru/js/linked-select-fields.js`
- CSS: use the same convention when CSS is added:
  `JPATH_SITE/media/lib_wt_otpravkapochtaru/css/{script_or_style_name}.css`

## Product Changes

- Moved linked-select JS from plugin media to library media:
  - from `plg_system_wt_otpravkapochtaru/media/js/linked-select-fields.js`
  - to `lib_webtolk_otpravkapochtaru/media/js/linked-select-fields.js`
- Added library WebAsset manifest:
  - `lib_webtolk_otpravkapochtaru/media/joomla.asset.json`
  - asset: `lib_wt_otpravkapochtaru.linked-select-fields`
  - uri: `lib_wt_otpravkapochtaru/js/linked-select-fields.js`
- Added library media install declaration:
  - `lib_webtolk_otpravkapochtaru/otpravkapochtaru.xml`
  - destination: `lib_wt_otpravkapochtaru`
- Removed plugin media install declaration from:
  - `plg_system_wt_otpravkapochtaru/wtotpravkapochtaru.xml`
- Updated field-side activation:
  - `lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php`
  - script name: `lib_wt_otpravkapochtaru.linked-select-fields`
  - fallback URI: `lib_wt_otpravkapochtaru/js/linked-select-fields.js`

## Verification

- PHP lint passed for `LinkedSelectField.php` using absolute path.
- JSON parse passed for library `joomla.asset.json`.
- XML parse passed for library and plugin manifests.
- Package build passed:
  - command: `phing -f D:\Dev\WT-Otpravkapochtaru-joomla-library\phing.xml "3. Package release"`
  - archive: `.packages/WT Otpravkapochtaru_3.0.0.zip`
  - archive size: `61869` bytes
  - file count: `41`
- ZIP media inspection found:
  - `lib_webtolk_otpravkapochtaru/media/joomla.asset.json`
  - `lib_webtolk_otpravkapochtaru/media/js/linked-select-fields.js`
- ZIP media inspection did not show plugin-owned linked-select media entries.

## Notes

- No CSS file currently exists for linked-select fields. When CSS is introduced,
  it should be installed through the same library media declaration under
  `media/lib_wt_otpravkapochtaru/css/`.
