# Публичный контракт фасада

Класс `Webtolk\Otpravkapochtaru\Otpravkapochtaru` является тонким Joomla-фасадом над `lapaygroup/russianpost`. Он читает параметры включенного системного плагина, строит транспорт на базе Joomla HTTP и возвращает готовые провайдеры SDK; операции с заказами, партиями, документами, тарифами, ОПС и трекингом выполняются методами LapayGroup, а не собственными методами фасада.

## Создание клиента

Конструктор фасада имеет сигнатуру:

```php
public function __construct(array|object|null $credentialsSource = null)
```

Без аргументов фасад читает параметры включенного системного плагина `wtotpravkapochtaru`.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
```

Для явной конфигурации можно передать массив, `Joomla\Registry\Registry`, готовый `Webtolk\Otpravkapochtaru\Joomla\CredentialsProvider` или совместимый объект с методом `params()`, который возвращает `Registry`. Массив автоматически оборачивается в `CredentialsProvider`, но в прикладных примерах используйте Joomla-способ: настройки хранятся в включенном системном плагине, а клиент создается без массива секретов.

Явная конфигурация поддерживает канонические ключи `access_token`, `auth_mode`, `user_key`, `user_login`, `user_password`, `tracking_login`, `tracking_password`, `http_timeout`. Для совместимости также читаются имена параметров системного плагина: `AccessToken`, `user_key_or_login_and_password`, `user_auth_key`.

Канонические ключи имеют приоритет над именами параметров плагина. Например, если одновременно переданы `access_token` и `AccessToken`, будет использован `access_token`.

Конструктор сразу создает REST- и tariff-провайдеры SDK, поэтому отсутствие REST-учетных данных может привести к `RuntimeException` уже при `new Otpravkapochtaru(...)`. SOAP-учетные данные трекинга проверяются лениво при вызове `trackingApi()`.

## Методы фасада

### `credentialsProvider(): CredentialsProvider`

Возвращает объект, который знает, откуда взяты параметры авторизации, и умеет построить значения для REST и SOAP-клиентов.

### `transport(): Psr18Transport`

Возвращает транспорт LapayGroup, собранный через Joomla HTTP и PSR-7 фабрики. Обычно этот метод нужен только для углубленной диагностики или собственных служебных вызовов.

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

Расчет тарифа для настроенного аккаунта также выполняйте через API «Отправка». `ParcelInfo` формирует корректное тело запроса, а метод возвращает `LapayGroup\RussianPost\TariffInfo`.

```php
<?php

declare(strict_types=1);

use LapayGroup\RussianPost\Enum\MailCategory;
use LapayGroup\RussianPost\Enum\MailType;
use LapayGroup\RussianPost\Enum\PaymentMethods;
use LapayGroup\RussianPost\ParcelInfo;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

// Библиотека читает параметры из включенного системного плагина Joomla.
$client = new Otpravkapochtaru();
$parcel = new ParcelInfo();
$parcel->setIndexFrom(410000);
$parcel->setIndexTo(685000);
$parcel->setMailType(MailType::PARCEL_POSTAL);
$parcel->setMailCategory(MailCategory::ORDINARY);
$parcel->setWeight(1000);
$parcel->setPaymentMethod(PaymentMethods::CASHLESS);

$tariff = $client->otpravkaApi()->getDeliveryTariff($parcel);
```

### `calculation(): Calculation`

Возвращает настроенный `LapayGroup\RussianPost\Providers\Calculation`. Это низкоуровневый тарификатор `delivery.pochta.ru`: для каждого `object_id` набор обязательных параметров нужно сначала получить из его справочника. Для тарифов аккаунта из обычного Joomla-расширения используйте `otpravkaApi()->getDeliveryTariff()` из предыдущего примера.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$countries = $client->calculation()->getCountryList();
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

Вспомогательный метод для информационного поля аккаунта в Joomla Form. Метод вызывает `otpravkaApi()->settings()`, а системный плагин и поля, поставляемые пакетом, используют его для безопасного отображения состояния подключения.

### `getApiLimit(): array`

Вспомогательный метод для информационного поля лимитов API в Joomla Form. В текущей версии LapayGroup SDK нет отдельного публичного метода для `/1.0/settings/limit`, поэтому фасад выполняет этот узкий служебный запрос через тот же авторизованный транспорт.
