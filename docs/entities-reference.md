# Сущности LapayGroup

Фасад `Otpravkapochtaru` не преобразует массивы в сущности SDK. Если метод `lapaygroup/russianpost` ожидает объект, вызывающий код должен создать этот объект самостоятельно и заполнить его через методы установки значений из SDK.

## Заказ

```php
<?php

declare(strict_types=1);

use LapayGroup\RussianPost\Entity\Order;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$order = new Order();
$order->setOrderNum('ORDER-10001');
$order->setPostOfficeCode('410000');
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
$result = $client->otpravkaApi()->editOrder($order, 123456);
```

`123456` здесь означает реальный ID уже созданного заказа, а `postoffice-code`, вид и категория отправления должны быть разрешены в вашем аккаунте.

## Получатель

```php
<?php

declare(strict_types=1);

use LapayGroup\RussianPost\Entity\Recipient;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$recipient = new Recipient();
$recipient->setAddress('455001, Челябинская область, Магнитогорск, Ленина, 1');
$recipient->setName('Иванов Иван');
$recipient->setPhone('79000000000');

$client = new Otpravkapochtaru();
$result = $client->otpravkaApi()->untrustworthyRecipient($recipient);
```

## Возвратное отправление

Для отдельного возвратного отправления SDK использует `LapayGroup\RussianPost\Entity\ReturnShipment` и вложенные `AddressReturn`. Заполняйте их методами установки значений из SDK и передавайте в методы `otpravkaApi()->createReturnShipment()` или `otpravkaApi()->editReturnShipment()`.
