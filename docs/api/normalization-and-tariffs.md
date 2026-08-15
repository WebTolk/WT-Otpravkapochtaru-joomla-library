# Тарифы и расчет доставки

Для тарифа настроенного аккаунта используйте API «Отправка» и `ParcelInfo`. Он собирает тело запроса для `OtpravkaApi::getDeliveryTariff()` и возвращает `TariffInfo` с суммами в копейках и минимальным/максимальным сроком доставки.

```php
<?php

declare(strict_types=1);

use LapayGroup\RussianPost\AddressList;
use LapayGroup\RussianPost\Enum\MailCategory;
use LapayGroup\RussianPost\Enum\MailType;
use LapayGroup\RussianPost\Enum\PaymentMethods;
use LapayGroup\RussianPost\ParcelInfo;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$api = $client->otpravkaApi();

$addresses = new AddressList();
$addresses->add('410000, Саратов, Московская улица, 1');
$normalizedAddress = $api->clearAddress($addresses);

$parcel = new ParcelInfo();
$parcel->setIndexFrom(410000);
$parcel->setIndexTo(685000);
$parcel->setMailType(MailType::PARCEL_POSTAL);
$parcel->setMailCategory(MailCategory::ORDINARY);
$parcel->setWeight(1000);
$parcel->setPaymentMethod(PaymentMethods::CASHLESS);

$tariff = $api->getDeliveryTariff($parcel);

// Amounts are in kopecks.
$totalRate = $tariff->getTotalRate();
$totalVat = $tariff->getTotalNds();
$minDays = $tariff->getDeliveryMinDays();
$maxDays = $tariff->getDeliveryMaxDays();
```

Набор допустимых `mail-type` и `mail-category` зависит от аккаунта и точки приема: получите его из `otpravkaApi()->shippingPoints()` перед построением формы отправки.
