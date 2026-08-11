# Return Shipments

Use `Webtolk\Otpravkapochtaru\Otpravkapochtaru` for return shipment workflows.

## Create Return Shipment

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();

$result = $client->createReturnShipment([
    'address-from' => [
        'index' => '410000',
        'region' => 'Saratov region',
        'place' => 'Saratov',
    ],
    'address-to' => [
        'index' => '685000',
        'region' => 'Magadan region',
        'place' => 'Magadan',
    ],
]);
```

## Supported Methods

- `createReturnShipment(array|object $shipment): array`
- `createReturnShipments(array $shipments): array`
- `editReturnShipment(int|string $id, array|object $shipment): array`
- `deleteReturnShipment(int|string $id): array`

The facade hydrates upstream SDK return entities internally when array payloads are passed.
