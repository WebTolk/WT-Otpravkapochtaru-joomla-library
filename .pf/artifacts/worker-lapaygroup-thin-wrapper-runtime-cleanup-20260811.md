# worker-lapaygroup-thin-wrapper-runtime-cleanup-20260811

## Verdict
- PASS: runtime facade dependencies on legacy fork namespaces were removed from `Otpravkapochtaru.php`, while keeping REST methods functional without SOAP usage.

## Files changed
- `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`
- `lib_webtolk_otpravkapochtaru/src/Joomla/CredentialsProvider.php` (new)
- `lib_webtolk_otpravkapochtaru/src/Joomla/UploadedFileSerializer.php` (new)
- `.pf/artifacts/worker-lapaygroup-thin-wrapper-runtime-cleanup-20260811.md`

## What changed
- Removed imports from old fork namespaces:
  - `Webtolk\Otpravkapochtaru\Configuration\*`
  - `Webtolk\Otpravkapochtaru\Entity\*`
  - `Webtolk\Otpravkapochtaru\Exception\*`
  - `Webtolk\Otpravkapochtaru\Dictionaries\*`
- Switched credentials access in facade constructor to helper:
  - `Webtolk\Otpravkapochtaru\Joomla\CredentialsProvider`
- Switched uploaded file normalization to helper:
  - `Webtolk\Otpravkapochtaru\Joomla\UploadedFileSerializer`
- Replaced old entity typed hints in facade public/private signatures with arrays/object-style payload handling:
  - `array|object` on public methods touching order/recipient/return-shipment payloads.
- Added generic payload adapter helpers to handle array payloads or object-likes with `toArray()`, `getParams()`, or `asArr()` methods.
- Replaced references to old fork exception classes with standard PHP exceptions (`\RuntimeException`, `\InvalidArgumentException`) in facade.
- Tracking behavior remains lazy in `trackingApi()` and still only instantiated on SOAP method calls.

## Verification commands and outputs
- `php -l "D:\Dev\WT-Otpravkapochtaru-joomla-library\lib_webtolk_otpravkapochtaru\src\Joomla\CredentialsProvider.php"`
  - `No syntax errors detected ... CredentialsProvider.php`
- `php -l "D:\Dev\WT-Otpravkapochtaru-joomla-library\lib_webtolk_otpravkapochtaru\src\Joomla\UploadedFileSerializer.php"`
  - `No syntax errors detected ... UploadedFileSerializer.php`
- `php -l "D:\Dev\WT-Otpravkapochtaru-joomla-library\lib_webtolk_otpravkapochtaru\src\Otpravkapochtaru.php"`
  - `No syntax errors detected ... Otpravkapochtaru.php`
- `rg -n "Webtolk\\\\Otpravkapochtaru\\\\(Configuration|Entity|Exception|Dictionaries|Request|SoapRequest|TrackingEntity)"`
  - No matches in `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`

## PHPStorm MCP usage
- Ran PHPStorm MCP repository query (`get_repositories`) and attempted inspections (`execute_tool` command calls), but inspection commands were cancelled by MCP response (`user cancelled MCP tool call`), so only PHP CLI lint was completed.

## Residual risks
- External callers in other modules may have relied on catching legacy `ConfigurationException`/`TransportException` from facade calls; those are now standard PHP exceptions.
- New helper in `Joomla\CredentialsProvider` assumes legacy sources expose `params()` if they are objects. If future legacy object does not provide `params()`, constructor will fall back to plugin params.
