# Публичный контракт фасада

Класс `Webtolk\Otpravkapochtaru\Otpravkapochtaru` является тонким Joomla-фасадом над `lapaygroup/russianpost`. Он читает параметры включенного системного плагина, строит транспорт на базе Joomla HTTP и возвращает готовые провайдеры SDK; операции с заказами, партиями, документами, тарифами, ОПС и трекингом выполняются методами LapayGroup, а не собственными методами фасада.

## Создание Клиента

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
```

Без аргументов фасад читает параметры включенного системного плагина `wtotpravkapochtaru`. Для явной конфигурации можно передать массив, `Joomla\Registry\Registry` или готовый `Webtolk\Otpravkapochtaru\Joomla\CredentialsProvider`; текущий код оборачивает массивы и `Registry` в `CredentialsProvider`, поэтому прямой `new Otpravkapochtaru($registry)` допустим.

```php
<?php

declare(strict_types=1);

use Joomla\Registry\Registry;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$config = new Registry([
    'access_token' => 'ACCESS_TOKEN',
    'auth_mode' => 'key',
    'user_key' => 'USER_AUTH_KEY',
    'tracking_login' => 'TRACKING_LOGIN',
    'tracking_password' => 'TRACKING_PASSWORD',
]);

$client = new Otpravkapochtaru($config);
```

## Методы Фасада

### `credentialsProvider(): CredentialsProvider`

Возвращает объект, который знает, откуда взяты параметры авторизации, и умеет построить значения для REST и SOAP-клиентов.

### `transport(): Psr18Transport`

Возвращает транспорт LapayGroup, собранный через Joomla HTTP и PSR-7 фабрики. Обычно этот метод нужен только для расширенной диагностики или собственных низкоуровневых вызовов.

### `otpravkaApi(): OtpravkaApi`

Возвращает настроенный `LapayGroup\RussianPost\Providers\OtpravkaApi`. Через него выполняются операции аккаунта, заказов, партий, документов, ОПС, возвратов и части справочников.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$settings = $client->otpravkaApi()->settings();
$points = $client->otpravkaApi()->shippingPoints();
```

### `calculation(): Calculation`

Возвращает настроенный `LapayGroup\RussianPost\Providers\Calculation`. Через него выполняются расчет тарифа, расчет срока доставки и запросы справочников тарифного сервиса.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();

$tariff = $client->calculation()->getTariff(
    27030,
    [
        'from' => 410012,
        'to' => 455001,
        'weight' => 1000,
    ],
    []
);
```

### `trackingApi(): Tracking`

Возвращает настроенный `LapayGroup\RussianPost\Providers\Tracking`. Метод создает SOAP-клиент только при первом обращении, поэтому отсутствие учетных данных трекинга проявится именно при вызове `trackingApi()` или методов полученного провайдера.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$history = $client->trackingApi()->getOperationsByRpo('80000000000000', 'RUS');
```

### `getAccountInfo(): array`

Joomla Form helper для информационного поля аккаунта. Метод вызывает `otpravkaApi()->settings()` и оставлен в фасаде, потому что системный плагин и bundled поля должны безопасно показывать состояние подключения.

### `getShippingPoints(): array`

Joomla Form helper для полей выбора ОПС, видов и категорий отправлений. Метод вызывает `otpravkaApi()->shippingPoints()` и возвращает массив, который затем разбирает `LinkedSelectOptionsService`.

### `getApiLimit(): array`

Joomla Form helper для информационного поля лимитов API. В текущей версии LapayGroup SDK нет отдельного публичного метода для `/1.0/settings/limit`, поэтому фасад выполняет этот узкий служебный запрос через тот же авторизованный транспорт.
