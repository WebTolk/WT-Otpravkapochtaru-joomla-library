# Normalization, Tariffs, And Delivery Periods

The 3.0.0 public package API exposes tariff operations through the facade. Dedicated public facade methods for REST normalization routes are not documented yet; add them before publishing normalization examples as supported API.

## Tariff

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();

$tariff = $client->getTariff(
    27030,
    [
        'from-index' => '410000',
        'to-index' => '685000',
        'mail-type' => 'POSTAL_PARCEL',
        'mail-category' => 'ORDINARY',
        'mass' => 1000,
    ],
    [2, 15]
);
```

## Tariff And Delivery Period

```php
$result = $client->getTariffAndDeliveryPeriod(
    27030,
    [
        'from-index' => '410000',
        'to-index' => '685000',
        'mail-type' => 'POSTAL_PARCEL',
        'mail-category' => 'ORDINARY',
        'mass' => 1000,
    ]
);

$period = $result['delivery-time'] ?? [];
```

Money values from Russian Post responses are normally expressed in kopecks.
