# Работа с SDK LapayGroup

Фасад `Otpravkapochtaru` не повторяет методы SDK. Он только подготавливает авторизацию из Joomla и возвращает настроенные провайдеры `lapaygroup/russianpost`, поэтому примеры ниже вызывают методы этих провайдеров напрямую.

## Основной REST API

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$api = $client->otpravkaApi();

$settings = $api->settings();
$shippingPoints = $api->shippingPoints();
$orders = $api->findOrderByShopId('ORDER-1001');
```

## Расчет тарифа

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();

$result = $client->calculation()->getTariffAndDeliveryPeriod(
    27030,
    [
        'from' => 410012,
        'to' => 455001,
        'weight' => 1000,
    ],
    []
);
```

## Трекинг

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$operations = $client->trackingApi()->getOperationsByRpo('80000000000000', 'RUS');
```

## Сущности SDK

Для методов, которые в коде LapayGroup вызывают `asArr()` или `getParams()`, нужно использовать сущности SDK. Фасад не преобразует произвольные массивы заказов, получателей или возвратов в эти объекты.
