# Возвраты

Возвратные отправления создаются методами `LapayGroup\RussianPost\Providers\OtpravkaApi`, полученного через `otpravkaApi()`. Для отдельного возврата без прямого ШПИ SDK ожидает массив объектов `ReturnShipment`.

```php
<?php

declare(strict_types=1);

use LapayGroup\RussianPost\Entity\AddressReturn;
use LapayGroup\RussianPost\Entity\ReturnShipment;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$from = new AddressReturn();
$from->setIndex('410012');
$from->setPlace('Саратов');
$from->setStreet('Московская');
$from->setHouse('109');

$shipment = new ReturnShipment();
$shipment->setAddressFrom($from);
$shipment->setMailType('UNDEFINED');
$shipment->setRecipientName('Иванов Иван');
$shipment->setSenderName('ООО Ромашка');
$shipment->setPostofficeCode('410012');

$client = new Otpravkapochtaru();
$result = $client->otpravkaApi()->createReturnShipment([$shipment->asArr()]);
```

Для возврата ранее созданного отправления используйте прямой метод SDK:

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$result = $client->otpravkaApi()->returnShipment('80000000000000', 'UNDEFINED');
```
