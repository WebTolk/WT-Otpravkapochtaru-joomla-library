# Developer API

This document describes the package boundary after the 3.0.0 thin-wrapper migration.

## Runtime Layers

| Layer | Public surface | Purpose |
| --- | --- | --- |
| Joomla facade | `Webtolk\Otpravkapochtaru\Otpravkapochtaru` | Stable entry point for account, orders, batches, tariffs, post offices, returns, documents, and tracking. |
| Joomla glue | `Webtolk\Otpravkapochtaru\Joomla\*` | Credentials, PSR-18 transport creation, and uploaded-file serialization for Joomla runtime. |
| Upstream SDK | `LapayGroup\RussianPost\*` | REST/SOAP providers and entities bundled into the release archive at build time. |

The removed fork-level request, SOAP, tracking, entity, dictionary, and configuration classes are not part of the current public API. Use the facade for application code. Use upstream SDK classes only when a task explicitly needs SDK-level objects.

## Basic Usage

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Joomla\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru(
    new CredentialsProvider([
        'access_token' => '...',
        'auth_mode' => 'key',
        'user_key' => '...',
    ])
);

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
```

If no credentials provider is passed, the facade reads enabled system plugin settings through Joomla.

## Payloads

Facade methods accept arrays for the common Joomla integration path. Where the upstream SDK requires entities, the facade hydrates `LapayGroup\RussianPost` objects internally and keeps legacy key styles tolerant for existing integrations.

For new code, prefer API field names such as `index-to`, `recipient-name`, `mail-type`, `mail-category`, and `mass`.

## SOAP Policy

Composer/GitHub builds require `ext-soap` because upstream tracking support depends on SOAP classes. Joomla package installation does not hard-fail when SOAP is missing; the installer shows a warning and tracking methods remain unavailable until SOAP is enabled.

## Related Pages

- [Facade method reference](facade-method-reference.md)
- [Thin wrapper architecture](thin-wrapper-architecture.md)
- [Low-level API status](low-level-api.md)
