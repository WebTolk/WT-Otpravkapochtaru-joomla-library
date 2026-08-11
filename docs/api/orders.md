# Orders And Recipient Checks

Use `Webtolk\Otpravkapochtaru\Otpravkapochtaru` for order workflows.

## Create Orders

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();

$created = $client->createOrders([
    [
        'order-num' => 'joomla-' . date('Ymd-His'),
        'recipient-name' => 'Ivanov Ivan',
        'tel-address' => '79000000000',
        'index-to' => '685000',
        'region-to' => 'Magadan region',
        'place-to' => 'Magadan',
        'street-to' => 'Lenina',
        'house-to' => '1',
        'mail-type' => 'POSTAL_PARCEL',
        'mail-category' => 'ORDINARY',
        'mass' => 1000,
    ],
]);
```

The facade accepts arrays and hydrates upstream SDK entities internally.

## Supported Methods

- `createOrders(array $orders): array`
- `editOrder(int|string $id, array|object $order): array`
- `findOrderById(int|string $id): array`
- `findOrderByShopId(int|string $shopId): array`
- `findOrderByRpo(string $rpo): array`
- `getRecipientReliability(array|object $recipient): array`
- `getRecipientsReliability(array $recipients): array`
- `deleteOrders(array $ids): array`
- `returnOrdersToNew(array $ids): array`

For new code, prefer API-style payload keys. Compatibility normalization for common legacy key styles is handled inside the facade.
