# Post Offices And Countries

Use the facade for post-office and country operations.

## Countries

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$countries = $client->getCountryList();
```

## Post Offices

- `searchPostOfficeByIndex(int|string $postalCode, ?string $latitude = null, ?string $longitude = null, ?string $currentDateTime = null, bool $filterByOfficeType = true, bool $ufpsPostalCode = false): array`
- `searchPostOfficeByAddress(string $address, ?string $top = null): array`
- `searchPostOfficeByCoordinates(string $latitude, string $longitude, ?string $top = null): array`
- `getPostOfficeServices(int|string $postalCode): array`
- `getPostalCodesInLocality(string $region, string $place, ?string $area = null, ?string $street = null): array`

The old package-owned country dictionary is no longer a public API; the facade delegates country lookup to the upstream SDK.
