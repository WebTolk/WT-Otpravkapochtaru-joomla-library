# WT Otpravkapochtaru Joomla Library

Пакет Joomla 5+ для работы с REST API отправки и SOAP-службами отслеживания Почты России.

Пакет устанавливает:

- `WT Otpravkapochtaru library` — PHP-библиотеку с пространством имён `Webtolk\Otpravkapochtaru`;
- `System - WT Otpravkapochtaru` — системный плагин Joomla для хранения учётных данных и проверки подключения.

## Возможности

- нормализация адресов, ФИО и телефонов;
- расчёт стоимости и срока доставки;
- создание, изменение, поиск и удаление черновиков отправлений;
- проверка надёжности получателя;
- создание партий и загрузка печатных документов;
- создание прямых и отдельных возвратов;
- поиск отделений по индексу, адресу и координатам;
- получение услуг отделения и индексов населённого пункта;
- одиночное и пакетное SOAP-отслеживание;
- локальный справочник стран без расходования лимита API.

## Требования

- Joomla 5 или новее;
- PHP 8.1 или новее;
- PHP SOAP для методов отслеживания;
- REST-токен и пользовательские реквизиты API отправки;
- отдельные SOAP-реквизиты, если требуется отслеживание.

## Установка и настройка

1. Установите `WT Otpravkapochtaru_3.0.0.zip` через раздел `Система → Установка → Расширения`.
2. Откройте `Система → Плагины`.
3. Найдите `System - WT Otpravkapochtaru`.
4. Заполните `access_token`.
5. Выберите режим пользовательской авторизации.
6. Укажите `user_key` либо пару `user_login`/`user_password`.
7. При необходимости заполните `tracking_login` и `tracking_password`.
8. Укажите время ожидания HTTP или оставьте значение 60 секунд.
9. Включите плагин, сохраните настройки и проверьте сведения об аккаунте.

## Быстрый старт: нормализация и расчёт доставки

После установки пространство имён регистрируется Joomla автоматически. Пример должен выполняться внутри загруженного приложения Joomla.

Расчёт выполняется только после нормализации адресов. Это исключает ситуацию, когда пользовательский текст содержит неверный индекс или неоднозначное название населённого пункта.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;
use Webtolk\Otpravkapochtaru\Request;

$credentials = new CredentialsProvider();
$request = new Request($credentials);
$client = new Otpravkapochtaru($credentials);

try {
    $normalizedAddresses = $request->postJson('/1.0/clean/address', [
        [
            'id' => 'sender',
            'original-address' => '410000, Саратов',
        ],
        [
            'id' => 'recipient',
            'original-address' => '685000, Магадан',
        ],
    ]);

    $senderAddress = $normalizedAddresses[0] ?? [];
    $recipientAddress = $normalizedAddresses[1] ?? [];

    $fromIndex = (string) ($senderAddress['index'] ?? '');
    $toIndex = (string) ($recipientAddress['index'] ?? '');

    if ($fromIndex === '' || $toIndex === '') {
        throw new UnexpectedValueException(
            'Почта России не вернула нормализованные индексы.',
        );
    }

    $tariffParams = [
        'from-index' => $fromIndex,
        'to-index' => $toIndex,
        'mail-type' => 'POSTAL_PARCEL',
        'mail-category' => 'ORDINARY',
        'mass' => 1000,
    ];

    $delivery = $client->getTariffAndDeliveryPeriod(
        objectId: 27030,
        params: $tariffParams,
    );

    $period = $delivery['delivery-time'] ?? [];

    var_dump([
        'route' => [
            'from' => $senderAddress,
            'to' => $recipientAddress,
        ],
        'price_kopecks' => $delivery['total-rate'] ?? null,
        'vat_kopecks' => $delivery['total-vat'] ?? null,
        'minimum_days' => $period['min-days'] ?? null,
        'maximum_days' => $period['max-days'] ?? null,
    ]);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException(
        'Почта России отклонила нормализацию или расчёт доставки.',
        0,
        $exception,
    );
}
```

Методы `getTariff()` и `getTariffAndDeliveryPeriod()` обращаются к одному маршруту. Не вызывайте их подряд для одного набора данных.

## Быстрый старт: создание заказа

В прикладном коде адрес, ФИО и телефон следует предварительно нормализовать. Полный сценарий приведён в [документации заказов](docs/api/orders.md#createorders).

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Entity\Order;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();

$order = Order::fromArray([
    'order-num' => 'joomla-' . date('Ymd-His'),
    'recipient-name' => 'Иванов Иван Иванович',
    'tel-address' => '+79000000000',
    'index-to' => '685000',
    'region-to' => 'Магаданская область',
    'place-to' => 'Магадан',
    'street-to' => 'проспект Ленина',
    'house-to' => '1',
    'mail-type' => 'POSTAL_PARCEL',
    'mail-category' => 'ORDINARY',
    'mass' => 1000,
]);

try {
    $result = $client->createOrders([$order]);
    $orderId = $result['result-ids'][0] ?? null;

    var_dump(['order_id' => $orderId, 'response' => $result]);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось создать отправление.', 0, $exception);
}
```

Сохраните `orderId` в записи заказа Joomla. Это избавляет от повторных поисковых запросов и упрощает изменение, помещение в партию и удаление.

## Документация

- [Техническая документация](docs/developer-api.md).
- [Все методы фасада](docs/facade-method-reference.md).
- [Аккаунт и настройки](docs/api/account-and-configuration.md).
- [Нормализация и тариф](docs/api/normalization-and-tariffs.md).
- [Заказы и получатели](docs/api/orders.md).
- [Партии и документы](docs/api/batches-and-documents.md).
- [Возвраты](docs/api/returns.md).
- [Отделения и справочники](docs/api/post-offices-and-dictionaries.md).
- [SOAP-отслеживание](docs/api/tracking.md).
- [Сущности данных](docs/entities-reference.md).
- [Низкоуровневый интерфейс](docs/low-level-api.md).
- [Обезличенные реальные ответы и JSON Schema](docs/api-schemas/otpravka/README.md).
- [Настройка пакета в Joomla](docs/joomla-user-guide.md).

## Ограничения

- Каждый REST-вызов расходует лимит аккаунта; чтение остатка лимита также является запросом.
- Доступность возвратов и отдельных печатных форм зависит от договора и настроек аккаунта.
- SOAP-отслеживание использует отдельные реквизиты.
- Наблюдаемые JSON Schema описывают фактические ответы одного прогона, а не все возможные варианты внешнего API.

## Разработка и проверка

Актуальная project flow папка теперь `.pf`. Старый каталог `.webtolk` сохранён как legacy/backup до ручной очистки после проверки миграции.

Проект использует общие средства контроля качества из `D:/.agents/tools/php-qa`.

```powershell
php -l script.php
powershell -NoProfile -ExecutionPolicy Bypass -File tools/qa/lint-php.ps1
php D:/.agents/tools/php-qa/vendor/bin/phpunit --configuration=phpunit.xml
php D:/.agents/tools/php-qa/vendor/bin/phpstan analyse --configuration=phpstan.neon
php D:/.agents/tools/php-qa/vendor/bin/php-cs-fixer fix --dry-run --diff --config=.php-cs-fixer.dist.php
php D:/.agents/tools/php-qa/vendor/bin/phpcs --standard=phpcs.xml
```

## Лицензия

GNU General Public License v3.0. См. [`LICENSE`](LICENSE).
