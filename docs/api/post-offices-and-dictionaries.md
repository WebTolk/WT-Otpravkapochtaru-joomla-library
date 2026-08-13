# ОПС и справочники

Методы ОПС находятся в `LapayGroup\RussianPost\Providers\OtpravkaApi`, а тарифные справочники находятся в `LapayGroup\RussianPost\Providers\Calculation`.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$api = $client->otpravkaApi();

$office = $api->searchPostOfficeByIndex('410012');
$nearAddress = $api->searchPostOfficeByAddress('Саратов, Московская, 109', 3);
$nearCoordinates = $api->searchPostOfficeByCoordinates([
    'latitude' => '51.533557',
    'longitude' => '46.034257',
    'top' => 3,
]);
$services = $api->getPostOfficeServices('410012');
$codes = $api->getPostalCodesInLocality('Саратов', 'Саратовская область');
```

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$countries = $client->calculation()->getCountryList();
```
