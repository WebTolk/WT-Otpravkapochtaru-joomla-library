# Тарифы и расчет доставки

Тонкий фасад не нормализует параметры тарифа. Передавайте в `LapayGroup\RussianPost\Providers\Calculation` те ключи, которые ожидает SDK и API Почты России.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$calculation = $client->calculation();

$params = [
    'from' => 410012,
    'to' => 455001,
    'weight' => 1000,
];

$tariff = $calculation->getTariff(27030, $params, []);
$period = $calculation->getTariffAndDeliveryPeriod(27030, $params, []);
$countries = $calculation->getCountryList();
```

Дополнительные услуги передаются третьим аргументом массивом; внутри SDK они преобразуются в строку параметра `service`.
