# Возвратные отправления

API различает прямой возврат, связанный с исходным штрихкодом, и отдельный возврат без исходного отправления. Доступность операций зависит от договора и настроек аккаунта.

Важно учитывать фактическое поведение транспорта: ответы с верхнеуровневыми полями `status=ERROR`, `code`, `error-code` или `error` превращаются в `TransportException`, однако массив `errors` во множественном числе может быть возвращён вызывающему коду как обычный результат. Поэтому примеры явно проверяют `errors`.

<a id="createreturnshipment"></a>
## `createReturnShipment(string $directBarcode, string $mailType = 'UNDEFINED'): array`

**Что делает.** Создаёт возврат для существующего прямого отправления.

**Зачем нужен.** Метод связывает обратную пересылку с РПО, которое было ранее создано и должно быть доступно текущему аккаунту.

**Как работает.** Выполняет `PUT /1.0/returns` с полями `direct-barcode` и `mail-type`.

| Параметр | Тип | Обязателен | Назначение |
| --- | --- | --- | --- |
| `$directBarcode` | `string` | да | Штрихкод исходного отправления. |
| `$mailType` | `string` | нет | Тип возвратного отправления; по умолчанию `UNDEFINED`. |

В наблюдаемом прогоне получен ответ `DIRECT_SHIPMENT_NOT_FOUND`: [пример](../api-schemas/otpravka/examples/create-return-shipment.response.json), [JSON Schema](../api-schemas/otpravka/schemas/create-return-shipment.response.schema.json).

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$barcode = preg_replace(
    '/\s+/',
    '',
    Factory::getApplication()->getInput()->getString('direct_barcode'),
) ?? '';

try {
    $result = $client->createReturnShipment($barcode, 'POSTAL_PARCEL');

    if (!empty($result['errors'])) {
        throw new RuntimeException(
            'API отклонил прямой возврат: ' . json_encode($result['errors'], JSON_UNESCAPED_UNICODE),
        );
    }

    var_dump($result);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось создать прямой возврат.', 0, $exception);
}
```

<a id="createreturnshipments"></a>
## `createReturnShipments(array $returnShipments): array`

**Что делает.** Создаёт один или несколько отдельных возвратов без привязки к прямому отправлению.

**Зачем нужен.** Такой сценарий применяется, когда обратная пересылка оформляется самостоятельно и договор разрешает услугу отдельного возврата.

**Как работает.** Каждый элемент преобразуется через `ReturnShipment::fromArray()->toArray()`, затем список отправляется запросом `PUT /1.0/returns/return-without-direct`.

| Параметр | Тип | Обязателен | Назначение |
| --- | --- | --- | --- |
| `$returnShipments` | `list<ReturnShipment\|array<string, mixed>>` | да | Список отдельных возвратов. |

Обязательные поля сущности: `mail-type`, `recipient-name`, `sender-name` и `address-from`. В адресе обязательны `index`, `place` и `region`.

В наблюдаемом прогоне аккаунт вернул `FREE_ER_ADDRESS_NOT_ENABLED`: [пример](../api-schemas/otpravka/examples/create-return-shipments.response.json), [JSON Schema](../api-schemas/otpravka/schemas/create-return-shipments.response.schema.json).

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Entity\AddressReturn;
use Webtolk\Otpravkapochtaru\Entity\ReturnShipment;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$returnShipment = ReturnShipment::fromArray([
    'mail-type' => 'POSTAL_PARCEL',
    'recipient-name' => 'Получатель возврата',
    'sender-name' => 'Отправитель возврата',
    'address-from' => AddressReturn::fromArray([
        'index' => '685000',
        'region' => 'Магаданская область',
        'place' => 'Магадан',
        'street' => 'проспект Ленина',
        'house' => '1',
    ]),
    'address-to' => AddressReturn::fromArray([
        'index' => '410000',
        'region' => 'Саратовская область',
        'place' => 'Саратов',
    ]),
]);

try {
    $result = $client->createReturnShipments([$returnShipment]);
    $errors = [];

    foreach ($result as $item) {
        if (!empty($item['errors'])) {
            $errors[] = $item['errors'];
        }
    }

    if ($errors !== []) {
        throw new RuntimeException(
            'API отклонил отдельный возврат: ' . json_encode($errors, JSON_UNESCAPED_UNICODE),
        );
    }

    var_dump($result);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось создать отдельный возврат.', 0, $exception);
}
```

<a id="editreturnshipment"></a>
## `editReturnShipment(ReturnShipment|array $returnShipment, string $rpo): array`

**Что делает.** Изменяет отдельный возврат по его РПО.

**Зачем нужен.** Метод позволяет исправить адрес, стороны возврата или параметры пересылки до окончательной обработки.

**Как работает.** Нормализует сущность и выполняет `POST /1.0/returns/{rpo}`.

| Параметр | Тип | Обязателен | Назначение |
| --- | --- | --- | --- |
| `$returnShipment` | `ReturnShipment\|array<string, mixed>` | да | Полное новое состояние возврата. |
| `$rpo` | `string` | да | РПО ранее созданного отдельного возврата. |

Реальная схема успешного ответа не зафиксирована: создание отдельного возврата было запрещено настройками тестового аккаунта.

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Entity\ReturnShipment;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$rpo = Factory::getApplication()->getInput()->getString('return_rpo');

$updated = ReturnShipment::fromArray([
    'mail-type' => 'POSTAL_PARCEL',
    'recipient-name' => 'Получатель возврата',
    'sender-name' => 'Отправитель возврата',
    'address-from' => [
        'index' => '685000',
        'region' => 'Магаданская область',
        'place' => 'Магадан',
    ],
    'address-to' => [
        'index' => '410000',
        'region' => 'Саратовская область',
        'place' => 'Саратов',
    ],
]);

try {
    $result = $client->editReturnShipment($updated, $rpo);

    if (!empty($result['errors'])) {
        throw new RuntimeException(json_encode($result['errors'], JSON_UNESCAPED_UNICODE));
    }

    var_dump($result);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось изменить отдельный возврат.', 0, $exception);
}
```

<a id="deletereturnshipment"></a>
## `deleteReturnShipment(string $rpo): array`

**Что делает.** Удаляет отдельный возврат.

**Зачем нужен.** Операция отменяет ошибочно созданную обратную пересылку до её дальнейшей обработки.

**Как работает.** Выполняет `DELETE /1.0/returns/delete-separate-return?barcode={rpo}`.

Реальная схема успешного ответа не зафиксирована по той же причине, что и для изменения возврата.

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$rpo = Factory::getApplication()->getInput()->getString('return_rpo');

if ($rpo === '') {
    throw new InvalidArgumentException('Не передан РПО отдельного возврата.');
}

try {
    $result = $client->deleteReturnShipment($rpo);

    if (!empty($result['errors'])) {
        throw new RuntimeException(json_encode($result['errors'], JSON_UNESCAPED_UNICODE));
    }

    var_dump($result);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось удалить отдельный возврат.', 0, $exception);
}
```
