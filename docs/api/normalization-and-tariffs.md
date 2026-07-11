# Нормализация данных, тариф и срок доставки

Расчёт тарифа имеет смысл только после проверки исходных данных. Индекс, город и улица, введённые человеком, могут противоречить друг другу; в таком случае стоимость будет рассчитана для ошибочного направления либо внешний API отклонит запрос. Библиотека пока не предоставляет отдельного метода фасада для нормализации, поэтому используются публичные методы `Request::postJson()`.

## Нормализация адреса перед расчётом

Маршрут `POST /1.0/clean/address` принимает список исходных адресов и возвращает список нормализованных записей. Важные строковые поля ответа: `index`, `region`, `place`, `street`, `house` и `quality-code`.

Наблюдаемые схемы:

- [адрес отправителя](../api-schemas/otpravka/schemas/clean-address-from.response.schema.json);
- [адрес получателя](../api-schemas/otpravka/schemas/clean-address-to.response.schema.json).

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
    $normalized = $request->postJson('/1.0/clean/address', [
        ['id' => 'from', 'original-address' => '410000, Саратов'],
        ['id' => 'to', 'original-address' => '685000, Магадан'],
    ]);

    $from = $normalized[0] ?? [];
    $to = $normalized[1] ?? [];

    if (empty($from['index']) || empty($to['index'])) {
        throw new UnexpectedValueException('API не вернул нормализованные индексы.');
    }

    $tariffParams = [
        'from-index' => (string) $from['index'],
        'to-index' => (string) $to['index'],
        'mail-type' => 'POSTAL_PARCEL',
        'mail-category' => 'ORDINARY',
        'mass' => 1000,
    ];

    $tariff = $client->getTariffAndDeliveryPeriod(27030, $tariffParams);

    var_dump([
        'from' => $from,
        'to' => $to,
        'cost_kopecks' => $tariff['total-rate'] ?? null,
        'vat_kopecks' => $tariff['total-vat'] ?? null,
        'delivery_period' => $tariff['delivery-time'] ?? null,
    ]);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Почта России отклонила нормализацию или расчёт.', 0, $exception);
}
```

<a id="gettariff"></a>
## `getTariff(int|string $objectId, array $params, array $services = []): array`

**Что делает.** Рассчитывает стоимость пересылки для указанного почтового продукта.

**Зачем нужен.** Метод применяется в корзине, форме заказа, административной части и фоновой синхронизации, когда системе нужна стоимость доставки до создания отправления.

**Как работает.** Метод добавляет к параметрам строковое поле `object`. Если переданы дополнительные услуги, их коды объединяются запятыми в поле `service`. Полученный массив отправляется запросом `POST /1.0/tariff`.

| Параметр | Тип | Обязателен | Назначение |
| --- | --- | --- | --- |
| `$objectId` | `int\|string` | да | Код тарифицируемого объекта, например `27030`. |
| `$params` | `array<string, scalar\|null>` | да | Индексы, тип и категория отправления, масса и дополнительные признаки. |
| `$services` | `list<int\|string>` | нет | Коды дополнительных услуг; по умолчанию список пуст. |

Минимально полезный набор параметров содержит `from-index`, `to-index`, `mail-type`, `mail-category` и `mass`. Денежные значения ответа обычно выражены в копейках.

Наблюдаемые данные: [пример ответа](../api-schemas/otpravka/examples/get-tariff-and-delivery-period.response.json) и [JSON Schema](../api-schemas/otpravka/schemas/get-tariff-and-delivery-period.response.schema.json).

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
    $addresses = $request->postJson('/1.0/clean/address', [
        ['id' => 'from', 'original-address' => '410000, Саратов'],
        ['id' => 'to', 'original-address' => '685000, Магадан'],
    ]);

    $fromIndex = (string) ($addresses[0]['index'] ?? '');
    $toIndex = (string) ($addresses[1]['index'] ?? '');

    if ($fromIndex === '' || $toIndex === '') {
        throw new UnexpectedValueException('Не удалось определить индексы.');
    }

    $tariff = $client->getTariff(
        27030,
        [
            'from-index' => $fromIndex,
            'to-index' => $toIndex,
            'mail-type' => 'POSTAL_PARCEL',
            'mail-category' => 'ORDINARY',
            'mass' => 1000,
        ],
        [2, 15],
    );

    var_dump([
        'total_rate' => $tariff['total-rate'] ?? null,
        'total_vat' => $tariff['total-vat'] ?? null,
    ]);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось рассчитать стоимость доставки.', 0, $exception);
}
```

<a id="gettariffanddeliveryperiod"></a>
## `getTariffAndDeliveryPeriod(int|string $objectId, array $params, array $services = []): array`

**Что делает.** Рассчитывает стоимость и возвращает срок доставки, если он присутствует в ответе Почты России.

**Зачем нужен.** Имя метода подчёркивает, что прикладной код ожидает не только сумму, но и диапазон дней.

**Как работает.** Реализация полностью совпадает с `getTariff()`: используется тот же `POST /1.0/tariff` и та же подготовка параметров. Вызов обоих методов подряд создаст два одинаковых сетевых запроса и не рекомендуется.

Параметры и возвращаемая схема совпадают с `getTariff()`. Поле срока обычно находится в `delivery-time` и содержит `min-days` и `max-days`.

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
    $addresses = $request->postJson('/1.0/clean/address', [
        ['id' => 'from', 'original-address' => '410000, Саратов'],
        ['id' => 'to', 'original-address' => '685000, Магадан'],
    ]);

    $params = [
        'from-index' => (string) ($addresses[0]['index'] ?? ''),
        'to-index' => (string) ($addresses[1]['index'] ?? ''),
        'mail-type' => 'POSTAL_PARCEL',
        'mail-category' => 'ORDINARY',
        'mass' => 1000,
    ];

    if ($params['from-index'] === '' || $params['to-index'] === '') {
        throw new UnexpectedValueException('Нормализация не вернула индексы.');
    }

    $result = $client->getTariffAndDeliveryPeriod(27030, $params);
    $period = $result['delivery-time'] ?? [];

    var_dump([
        'price_kopecks' => $result['total-rate'] ?? null,
        'minimum_days' => $period['min-days'] ?? null,
        'maximum_days' => $period['max-days'] ?? null,
    ]);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось рассчитать стоимость и срок доставки.', 0, $exception);
}
```

## Нормализация ФИО и телефона

Перед созданием заказа полезно дополнительно выполнить:

- `POST /1.0/clean/physical` для ФИО;
- `POST /1.0/clean/phone` для номера телефона.

Реальные наблюдаемые контракты доступны в [примере ФИО](../api-schemas/otpravka/examples/clean-physical.response.json), [схеме ФИО](../api-schemas/otpravka/schemas/clean-physical.response.schema.json), [примере телефона](../api-schemas/otpravka/examples/clean-phone.response.json) и [схеме телефона](../api-schemas/otpravka/schemas/clean-phone.response.schema.json).
