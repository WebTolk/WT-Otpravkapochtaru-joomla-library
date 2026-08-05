# WT Otpravkapochtaru for Joomla

`WT Otpravkapochtaru` is a Joomla 5+ package that helps Joomla extensions work with Russian Post's Otpravka REST API and the SOAP tracking service. The package contains a library and a system plugin that stores connection settings.

This document is prepared for the SW JProjects project page. It does not replace the full developer reference in the repository. Its purpose is to explain the practical workflow: connect the account, normalize user data, calculate delivery, create a shipment, build a batch, generate documents, create returns and request tracking data.

Official Russian Post documentation is available at: <https://otpravka.pochta.ru/specification#/main>.

## What the Library Covers

The official Otpravka specification is organized around large API areas: authorization, data preparation, orders, batches, documents, returns, settings, post office lookup, property dictionaries and additional service modules. The public library facade follows this model where it is useful for a regular Joomla integration.

Supported areas:

- reading account settings, shipping points and the current API request limit;
- normalizing addresses, personal names and phone numbers before shipment creation;
- calculating tariff and delivery period through the current Russian Post REST method;
- creating, finding, editing, deleting and returning orders to the New state;
- checking recipient reliability;
- creating batches and reading batch/order data;
- generating the document package and the F103 form;
- creating, editing and deleting return shipments;
- searching post offices by postal code, address and coordinates;
- reading post office services;
- using a local country dictionary;
- requesting SOAP tracking operations and working with tracking tickets.

Not exposed as dedicated public methods:

- batch archive and long-term archive;
- time slots and booking;
- claims for additional services;
- API user sessions;
- every additional printed form from the official specification except the document package and F103.

This is intentional. The package covers the main shipping and tracking workflow without turning the Joomla integration into a full mirror of the official specification.

## Installation and Setup

Install the ZIP package with the standard Joomla extension manager. After installation, open the `System - WT Otpravkapochtaru` plugin and fill in the connection settings:

- application access token;
- authorization mode;
- user authorization key or login/password pair;
- separate SOAP tracking credentials if tracking is required;
- HTTP request timeout.

For production sites, keep real credentials only in Joomla settings or in a protected configuration provider. Do not place them in code, examples, logs or public documentation.

## Basic Shipping Workflow

Do not create a shipment directly from raw form input. First, transform user-entered data into the shape expected by Russian Post.

1. Normalize sender and recipient addresses.
2. Normalize recipient name and phone.
3. Use the normalized postal code and address in tariff calculation.
4. Build the order payload from normalized data.
5. Create the order in the New state.
6. After checking several orders, build a batch.
7. Generate printable documents.

This order reduces API errors and makes the integration easier to debug.

## Example: Normalization and Delivery Calculation

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$credentials = new CredentialsProvider([
    'access_token' => 'YOUR_ACCESS_TOKEN',
    'auth_mode' => 'login_password',
    'user_login' => 'YOUR_USER_LOGIN',
    'user_password' => 'YOUR_USER_PASSWORD',
]);

$client = new Otpravkapochtaru($credentials);

$fromAddress = $client->cleanAddress('410000, Saratov, Moskovskaya street, 1');
$toAddress = $client->cleanAddress('685000, Magadan, Lenina avenue, 1');
$recipient = $client->cleanPhysical('Ivanov Ivan Ivanovich');
$phone = $client->cleanPhone('+7 900 000-00-00');

$tariff = $client->getTariffAndDeliveryPeriod(
    '23030',
    [
        'index-from' => $fromAddress[0]['index'] ?? '410000',
        'index-to' => $toAddress[0]['index'] ?? '685000',
        'mail-category' => 'ORDINARY',
        'mail-type' => 'POSTAL_PARCEL',
        'mass' => 500,
        'fragile' => false,
        'sms-notice-recipient' => 0,
        'with-order-of-notice' => false,
        'with-simple-notice' => false,
    ]
);

echo 'Recipient: ' . ($recipient[0]['original-fio'] ?? 'not detected') . PHP_EOL;
echo 'Phone: ' . ($phone[0]['phone'] ?? 'not detected') . PHP_EOL;
echo 'Price: ' . (($tariff['total-rate'] ?? 0) / 100) . ' RUB' . PHP_EOL;
echo 'Delivery period: ' . ($tariff['delivery-time'] ?? 'not calculated') . PHP_EOL;
```

API money values are usually returned in kopecks. Divide them by `100` before displaying them to a user.

## Example: Creating an Order

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Entity\Order;
use Webtolk\Otpravkapochtaru\Entity\Recipient;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru(new CredentialsProvider([
    'access_token' => 'YOUR_ACCESS_TOKEN',
    'auth_mode' => 'login_password',
    'user_login' => 'YOUR_USER_LOGIN',
    'user_password' => 'YOUR_USER_PASSWORD',
]));

$normalizedAddress = $client->cleanAddress('685000, Magadan, Lenina avenue, 1');
$normalizedPerson = $client->cleanPhysical('Ivanov Ivan Ivanovich');
$normalizedPhone = $client->cleanPhone('+7 900 000-00-00');

$recipient = Recipient::create([
    'address-type-to' => 'DEFAULT',
    'index-to' => $normalizedAddress[0]['index'] ?? '685000',
    'region-to' => $normalizedAddress[0]['region'] ?? 'Magadan region',
    'place-to' => $normalizedAddress[0]['place'] ?? 'Magadan',
    'street-to' => $normalizedAddress[0]['street'] ?? 'Lenina avenue',
    'house-to' => $normalizedAddress[0]['house'] ?? '1',
    'recipient-name' => $normalizedPerson[0]['original-fio'] ?? 'Ivanov Ivan Ivanovich',
    'tel-address' => $normalizedPhone[0]['phone'] ?? '79000000000',
]);

$order = Order::create([
    'order-num' => 'JSHOP-10001',
    'mail-type' => 'POSTAL_PARCEL',
    'mail-category' => 'ORDINARY',
    'mass' => 500,
    'transport-type' => 'SURFACE',
    'payment-method' => 'CASHLESS',
    'recipient' => $recipient->toArray(),
]);

$created = $client->createOrders([$order]);

foreach ($created as $item) {
    echo 'Created API order: ' . ($item['result-ids'][0] ?? 'without id') . PHP_EOL;
}
```

Use `editOrder()` to update an existing order and `deleteOrders()` to remove orders from the New state. Use `returnOrdersToNew()` when an order has to be moved back to New.

## Batches and Documents

When orders are checked and ready to be handed over to a post office, create a batch:

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru(new CredentialsProvider([
    'access_token' => 'YOUR_ACCESS_TOKEN',
    'auth_mode' => 'login_password',
    'user_login' => 'YOUR_USER_LOGIN',
    'user_password' => 'YOUR_USER_PASSWORD',
]));

$batch = $client->createBatch([123456789, 123456790]);
$batchName = $batch['batch-name'] ?? null;

if ($batchName !== null) {
    $zip = $client->generateDocumentPackage($batchName);
    file_put_contents(JPATH_ROOT . '/tmp/' . $batchName . '.zip', $zip['body']);
}
```

Document methods return binary data. Store it in a protected location and do not send it to a browser without proper `Content-Type` and `Content-Disposition` headers.

## Returns

A return shipment can be created for a direct shipment barcode or as a separate return shipment. Use the `ReturnShipment` entity when you want to assemble the payload explicitly and reuse it in tests.

Main methods:

- `createReturnShipment()` creates a return for a previously created shipment;
- `createReturnShipments()` creates separate return shipments;
- `editReturnShipment()` updates a separate return shipment;
- `deleteReturnShipment()` deletes a separate return shipment.

## Post Offices and Dictionaries

Post office lookup methods are useful before order creation. They can validate a postal code, find a nearby office and list available services.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru(new CredentialsProvider([
    'access_token' => 'YOUR_ACCESS_TOKEN',
    'auth_mode' => 'login_password',
    'user_login' => 'YOUR_USER_LOGIN',
    'user_password' => 'YOUR_USER_PASSWORD',
]));

$office = $client->searchPostOfficeByIndex('685000');
$services = $client->getPostOfficeServices('685000');

var_dump($office, $services);
```

The country list is local and does not spend the REST API request limit.

## SOAP Tracking

The Otpravka REST API is used for shipment preparation. Tracking operations use Russian Post's SOAP service, so they require separate credentials.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru(new CredentialsProvider([
    'tracking_login' => 'YOUR_TRACKING_LOGIN',
    'tracking_password' => 'YOUR_TRACKING_PASSWORD',
]));

$operations = $client->getOperationsByRpo('12345678901234');

foreach ($operations as $operation) {
    echo ($operation['operationName'] ?? 'Operation') . PHP_EOL;
}
```

If the account has no SOAP tracking access, REST shipping scenarios may still work.

## Real Response Schemas

The repository contains an appendix with observed JSON Schemas captured from real shipping requests. The schemas are stored in `docs/api-schemas/otpravka/` and are not included in the Joomla ZIP package.

Use these schemas as engineering evidence for documentation and tests, but do not treat them as a complete official specification. The API may return additional fields when Russian Post changes a contract or enables new account features.

## Further Reading

- `README.md` — quick start and installation.
- `docs/developer-api.md` — full technical reference.
- `docs/facade-method-reference.md` — public facade method reference.
- `docs/api/*.md` — detailed scenario chapters.
- `docs/entities-reference.md` — `Order`, `Recipient`, `ReturnShipment` and other payload entities.
- `docs/low-level-api.md` — low-level transport, SOAP and credential provider details.

## Publication Checklist

For SW JProjects, use the HTML fragment generated from this document. The text is divided into human-readable sections, avoids heavy tables and fits a project page: purpose first, then workflow, examples and limitations.

Before publishing, check that:

- the project remains unpublished if the extension is not ready for public download;
- the text contains no real access keys, customer phones, barcodes or order addresses;
- links to the full documentation point to a public repository or prepared website pages;
- plugin settings screenshots are uploaded to the language-specific project galleries.
