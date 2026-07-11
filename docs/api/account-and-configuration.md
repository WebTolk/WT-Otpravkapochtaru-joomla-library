# Аккаунт и настройки подключения

Методы этой главы обращаются к REST API отправки Почты России. Каждый вызов, включая чтение остатка лимита, расходует один запрос из лимита аккаунта.

Во всех примерах предполагается, что пакет установлен, системный плагин `System - WT Otpravkapochtaru` включён, а его параметры заполнены. Пространство имён библиотеки регистрируется Joomla из манифеста, поэтому дополнительный `require` не нужен.

<a id="construct"></a>
## `__construct(?CredentialsProvider $credentialsProvider = null)`

**Что делает.** Создаёт фасад и связывает его с REST- и SOAP-клиентами.

**Зачем нужен.** Это единая точка входа для прикладного кода компонента, плагина, модуля или консольной команды Joomla.

**Как работает.** Если провайдер не передан, фасад создаёт `CredentialsProvider`, который при первом обращении читает параметры включённого системного плагина. Один и тот же провайдер используется для REST и SOAP.

| Параметр | Тип | Обязателен | Назначение |
| --- | --- | --- | --- |
| `$credentialsProvider` | `CredentialsProvider\|null` | нет | Явные параметры для теста или провайдер настроек Joomla. |

Возвращаемого значения нет; результатом является объект `Otpravkapochtaru`.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$credentials = new CredentialsProvider();
$client = new Otpravkapochtaru($credentials);
```

<a id="getaccountinfo"></a>
## `getAccountInfo(): array`

**Что делает.** Получает полные настройки и возможности текущего аккаунта.

**Зачем нужен.** Метод позволяет проверить авторизацию, доступные продукты, точки сдачи и реквизиты до выполнения операций с заказами.

**Как работает.** Выполняет `GET /1.0/settings` на основном адресе API и возвращает разобранный JSON как ассоциативный массив.

| Результат | Тип | Содержание |
| --- | --- | --- |
| Возвращаемое значение | `array<string, mixed>` | Настройки аккаунта, доступные продукты, адреса и точки сдачи. |

Наблюдаемые данные: [пример ответа](../api-schemas/otpravka/examples/get-account-info.response.json) и [JSON Schema](../api-schemas/otpravka/schemas/get-account-info.response.schema.json).

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();

try {
    $account = $client->getAccountInfo();

    var_dump([
        'organization' => $account['org-name'] ?? null,
        'shipping_points' => $account['shipping-points'] ?? [],
        'available_products' => $account['user-available-products'] ?? [],
    ]);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось получить настройки аккаунта.', 0, $exception);
}
```

<a id="getsettings"></a>
## `getSettings(): array`

**Что делает.** Возвращает исходный массив настроек аккаунта.

**Зачем нужен.** Это смысловой синоним `getAccountInfo()`, удобный в коде, где данные рассматриваются именно как настройки.

**Как работает.** Обращается к тому же маршруту `GET /1.0/settings`; отдельного сетевого преимущества у последовательного вызова обоих методов нет.

| Результат | Тип | Содержание |
| --- | --- | --- |
| Возвращаемое значение | `array<string, mixed>` | Тот же контракт, что у `getAccountInfo()`. |

Схема совпадает со схемой [`getAccountInfo()`](../api-schemas/otpravka/schemas/get-account-info.response.schema.json).

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();

try {
    $settings = $client->getSettings();
    $defaultPoint = $settings['shipping-points'][0] ?? null;

    var_dump($defaultPoint);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось прочитать настройки отправителя.', 0, $exception);
}
```

<a id="getshippingpoints"></a>
## `getShippingPoints(): array`

**Что делает.** Получает точки сдачи, доступные текущему пользователю API.

**Зачем нужен.** Результат помогает выбрать отделение, определить адрес возврата и проверить, какие продукты разрешены для конкретной точки сдачи.

**Как работает.** Выполняет `GET /1.0/user-shipping-points` и возвращает список объектов.

| Результат | Тип | Содержание |
| --- | --- | --- |
| Возвращаемое значение | `list<array<string, mixed>>` | Точки сдачи, индексы, адреса возврата и доступные продукты. |

Наблюдаемые данные: [пример ответа](../api-schemas/otpravka/examples/get-shipping-points.response.json) и [JSON Schema](../api-schemas/otpravka/schemas/get-shipping-points.response.schema.json).

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();

try {
    $points = $client->getShippingPoints();

    foreach ($points as $point) {
        var_dump([
            'index' => $point['operator-postcode'] ?? null,
            'address' => $point['ops-address'] ?? null,
            'return_address' => $point['return-address'] ?? null,
        ]);
    }
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось получить точки сдачи.', 0, $exception);
}
```

<a id="getapilimit"></a>
## `getApiLimit(): array`

**Что делает.** Получает сведения о текущем ограничении числа запросов.

**Зачем нужен.** Метод полезен для редкой диагностики или для планировщика, который должен решить, можно ли начинать крупную пакетную операцию.

**Как работает.** Выполняет `GET /1.0/settings/limit`. Сам этот вызов также расходует лимит, поэтому его нельзя выполнять перед каждой операцией или использовать как счётчик после каждого запроса.

| Результат | Тип | Содержание |
| --- | --- | --- |
| Возвращаемое значение | `array<string, mixed>` | Поля лимита в форме, которую вернул внешний API. |

В реальном прогоне этот метод намеренно не вызывался, поэтому наблюдаемая схема в репозитории отсутствует.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();

try {
    // Такой запрос следует выполнять только при действительной необходимости.
    $limit = $client->getApiLimit();

    var_dump($limit);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось получить сведения о лимите API.', 0, $exception);
}
```
