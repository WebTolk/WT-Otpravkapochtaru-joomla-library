# Заказы

Заказы обрабатываются напрямую через `LapayGroup\RussianPost\Providers\OtpravkaApi`, который возвращает метод `Otpravkapochtaru::otpravkaApi()`. Фасад не принимает массивы заказов и не нормализует их в сущности SDK.

## Создание Заказов

В поставляемом коде LapayGroup метод `createOrders($orders)` передает массив дальше в общий вызов API, поэтому передавайте данные в форме, которую ожидает SDK и API Почты России; если вы строите заказ через `Order`, преобразуйте его в массив самостоятельно через `asArr()`.

```php
<?php

declare(strict_types=1);

use LapayGroup\RussianPost\Entity\Order;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$order = new Order();
$order->setIndexTo('455001');
$order->setRecipientName('Иванов Иван');
$order->setTelAddress('79000000000');
$order->setMailType('POSTAL_PARCEL');
$order->setMailCategory('ORDINARY');
$order->setMass(1000);

$client = new Otpravkapochtaru();
$created = $client->otpravkaApi()->createOrders([$order->asArr()]);
```

## Поиск И Изменение

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$api = $client->otpravkaApi();

$byId = $api->findOrderById(123456);
$byShopId = $api->findOrderByShopId('ORDER-1001');
$byRpo = $api->findOrderByRpo('80000000000000');
```

Для `editOrder($order, $id)` SDK вызывает `$order->asArr()`, поэтому туда нужен объект `LapayGroup\RussianPost\Entity\Order`, а не произвольный массив.

## Удаление И Возврат В Новые

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$client->otpravkaApi()->deleteOrders([123456]);
$client->otpravkaApi()->returnToNew([123456]);
```
