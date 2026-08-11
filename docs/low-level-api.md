# Low-Level API Status

This page is intentionally short after the 3.0.0 thin-wrapper migration.

The old fork-level request, SOAP, tracking, entity, dictionary, and configuration classes are no longer a public API of this Joomla package. Application code should use the facade:

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
```

For operations that need SDK objects directly, use the upstream package classes shipped in the release archive under `lib_webtolk_otpravkapochtaru/src/libraries/vendor/lapaygroup/russianpost/src`.

## Current Public Boundary

- `Webtolk\Otpravkapochtaru\Otpravkapochtaru` remains the Joomla facade.
- `Webtolk\Otpravkapochtaru\Joomla\CredentialsProvider` reads Joomla plugin settings or explicit arrays/registries.
- `LapayGroup\RussianPost\*` is the upstream SDK surface used by the facade.
- REST normalization routes that are not exposed by facade methods need a new facade method before they are documented as supported package API.

The pre-3.0 low-level examples were retained in Git history only. Do not copy them into new code.
