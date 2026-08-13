# Трекинг

Трекинг выполняется через `LapayGroup\RussianPost\Providers\Tracking`, который фасад создает лениво при вызове `trackingApi()`. Для работы нужны параметры `tracking_login` и `tracking_password`, а также PHP extension `soap`.

## История Одного Отправления

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$operations = $client->trackingApi()->getOperationsByRpo('80000000000000', 'RUS');
```

## Наложенный Платеж

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$events = $client->trackingApi()->getNpayInfo('80000000000000', 'RUS');
```

## Пакетный Запрос

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$tickets = $client->trackingApi()->getTickets(['80000000000000'], 'RUS');
$items = $client->trackingApi()->getOperationsByTicket((string) ($tickets['tickets'][0] ?? ''));
```
