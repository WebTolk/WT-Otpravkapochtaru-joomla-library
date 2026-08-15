# Заказы

Заказы обрабатываются напрямую через `LapayGroup\RussianPost\Providers\OtpravkaApi`, который возвращает метод `Otpravkapochtaru::otpravkaApi()`. Фасад не принимает массивы заказов и не нормализует их в сущности SDK.

## Создание заказов

В поставляемом коде LapayGroup метод `createOrders($orders)` сериализует переданный массив как есть. Поэтому, если вы строите заказ через `Order`, обязательно преобразуйте его в массив через `asArr()` до вызова API. Перед созданием заказа выберите доступные для своего аккаунта точку приема, `mail-type` и `mail-category` из `shippingPoints()`.

```php
<?php

declare(strict_types=1);

use LapayGroup\RussianPost\Entity\Order;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$order = new Order();
$order->setOrderNum('ORDER-10001');
$order->setPostOfficeCode('410000'); // Select an account-enabled operator postcode.
$order->setIndexTo('455001');
$order->setRegionTo('Челябинская область');
$order->setPlaceTo('Магнитогорск');
$order->setStreetTo('Ленина');
$order->setHouseTo('1');
$order->setRecipientName('Иванов Иван Иванович');
$order->setTelAddress('79000000000');
$order->setMailType('POSTAL_PARCEL');
$order->setMailCategory('ORDINARY');
$order->setMass(1000);

$client = new Otpravkapochtaru();
$created = $client->otpravkaApi()->createOrders([$order->asArr()]);
```

`createOrders()` меняет состояние аккаунта: не запускайте пример с демонстрационным `order-num` на production. Подставьте уникальный номер заказа и допустимые для вашей точки приема значения.

## Поиск и изменение

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

## Удаление и возврат в новые

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$client->otpravkaApi()->deleteOrders([123456]);
$client->otpravkaApi()->returnToNew([123456]);
```
