# Release package archive audit
Run: release-readiness-audit-20260811
Task: t26-release-package-archive-audit

## ZIP presence
- exists: yes
- path: D:\Dev\WT-Otpravkapochtaru-joomla-library\.packages\WT Otpravkapochtaru_3.0.0.zip
- size_bytes: 62097
- entry_count: 41
## ZIP entry checks
- contains script.php: True
- contains library media joomla.asset.json: True
- contains library media js entries: True
- old plugin-owned media entries present: False
- library media js entries in zip:
  - lib_webtolk_otpravkapochtaru/media/js/linked-select-fields.js
- linked-select matching entries in zip:
  - lib_webtolk_otpravkapochtaru/media/js/linked-select-fields.js
  - lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php
  - lib_webtolk_otpravkapochtaru/src/Service/LinkedSelectOptionsService.php

## Manifest checks
- pkg_lib_wt_otpravkapochtaru.xml: exists
- lib_webtolk_otpravkapochtaru/otpravkapochtaru.xml: exists
  - destination:
  - folder: src
- plg_system_wt_otpravkapochtaru/wtotpravkapochtaru.xml: exists
  - has media block: False
  - has linked-select mention: False
- lib_webtolk_otpravkapochtaru/media/joomla.asset.json: exists
- lib_webtolk_otpravkapochtaru/media/js/linked-select-fields.js: exists

## Verdict
verdict: needs-fix

## Residual risk
- One or more required conditions are failing in archive or manifests; package may need rebuild or manifest correction.
