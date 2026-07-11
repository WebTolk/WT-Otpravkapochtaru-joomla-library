# SOAP-отслеживание

Методы этой главы используют отдельные SOAP-службы Почты России и отдельные параметры `tracking_login`/`tracking_password`. REST-токен не заменяет эти учётные данные. Для работы требуется расширение PHP SOAP.

Фасад возвращает обычные массивы: SOAP-объекты рекурсивно преобразуются перед возвратом. Сетевые и SOAP-ошибки оборачиваются в `TrackingException`.

<a id="getoperationsbyrpo"></a>
## `getOperationsByRpo(string $rpo, string $lang = 'RUS'): array`

**Что делает.** Получает историю операций одного РПО.

**Зачем нужен.** Метод позволяет показать состояние доставки и хронологию перемещения в заказе Joomla.

**Как работает.** Создаёт клиент SOAP 1.2 по `rtm34_wsdl.xml`, формирует `OperationHistoryRequest` и `AuthorizationHeader`, вызывает `getOperationHistory`, затем возвращает список записей `historyRecord`.

| Параметр | Тип | По умолчанию | Назначение |
| --- | --- | --- | --- |
| `$rpo` | `string` | — | Почтовый идентификатор. |
| `$lang` | `string` | `RUS` | Язык ответа SOAP-службы. |

Возвращаемый тип: `list<array<string, mixed>>`. Реальная схема не зафиксирована, поскольку для тестового окружения не были предоставлены SOAP-реквизиты.

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\TrackingException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$rpo = preg_replace('/\s+/', '', Factory::getApplication()->getInput()->getString('rpo')) ?? '';

try {
    $operations = $client->getOperationsByRpo($rpo, 'RUS');

    foreach ($operations as $operation) {
        var_dump([
            'date' => $operation['OperationParameters']['OperDate'] ?? null,
            'operation' => $operation['OperationParameters']['OperType']['Name'] ?? null,
            'place' => $operation['AddressParameters']['OperationAddress']['Description'] ?? null,
        ]);
    }
} catch (TrackingException $exception) {
    throw new RuntimeException('Не удалось получить историю РПО.', 0, $exception);
}
```

<a id="getnpayinfo"></a>
## `getNpayInfo(string $rpo, string $lang = 'RUS'): array`

**Что делает.** Получает события почтового перевода или наложенного платежа, связанные с РПО.

**Зачем нужен.** Логистический статус не сообщает о движении денег, поэтому финансовые события запрашиваются отдельно.

**Как работает.** Через тот же SOAP 1.2 клиент вызывает `PostalOrderEventsForMail`. Параметры авторизации передаются SOAP-узлами, а РПО и язык — XML-узлом `PostalOrderEventsForMailInput`.

Возвращаемый тип: `list<array<string, mixed>>`.

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\TrackingException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$rpo = Factory::getApplication()->getInput()->getString('rpo');

try {
    $events = $client->getNpayInfo($rpo);

    foreach ($events as $event) {
        var_dump([
            'name' => $event['EventName'] ?? null,
            'date' => $event['EventDate'] ?? null,
            'amount' => $event['Amount'] ?? null,
        ]);
    }
} catch (TrackingException $exception) {
    throw new RuntimeException('Не удалось получить события наложенного платежа.', 0, $exception);
}
```

<a id="gettickets"></a>
## `getTickets(array $rpoList, string $lang = 'RUS'): array`

**Что делает.** Создаёт заявки на пакетное получение истории нескольких РПО.

**Зачем нужен.** Пакетная служба эффективнее множества одиночных SOAP-вызовов при синхронизации большого числа заказов.

**Как работает.** Разбивает входной список на части не более 500 РПО, создаёт SOAP 1.1 клиент по `fc_wsdl.xml` и вызывает `getTicket` для каждой части.

| Ключ результата | Тип | Назначение |
| --- | --- | --- |
| `tickets` | `list<string>` | Созданные номера заявок. |
| `not_create` | `list<string>` | РПО из частей, для которых служба не вернула заявку. |

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Exception\TrackingException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$rpoList = [
    '80000000000001',
    '80000000000002',
    '80000000000003',
];

try {
    $result = $client->getTickets($rpoList, 'RUS');

    var_dump([
        'tickets' => $result['tickets'],
        'not_created' => $result['not_create'],
    ]);
} catch (TrackingException $exception) {
    throw new RuntimeException('Не удалось создать пакетную заявку.', 0, $exception);
}
```

<a id="getoperationsbyticket"></a>
## `getOperationsByTicket(string $ticket): array`

**Что делает.** Получает готовый результат пакетной заявки.

**Зачем нужен.** `getTickets()` только создаёт заявку; история появляется после её обработки внешней службой.

**Как работает.** Передаёт номер заявки, логин и пароль методу SOAP `getResponseByTicket`. Если результат ещё пуст, возвращает `[]`; иначе нормализует каждый `Item`.

Возвращаемый тип: `list<array<string, mixed>>`.

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\TrackingException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$ticket = Factory::getApplication()->getInput()->getString('tracking_ticket');

try {
    $items = $client->getOperationsByTicket($ticket);

    if ($items === []) {
        var_dump('Заявка ещё обрабатывается или не содержит результатов.');
    }

    foreach ($items as $item) {
        var_dump([
            'barcode' => $item['Barcode'] ?? null,
            'history' => $item['OperationHistoryData']['historyRecord'] ?? [],
        ]);
    }
} catch (TrackingException $exception) {
    throw new RuntimeException('Не удалось получить пакетный результат.', 0, $exception);
}
```

## Пакетная последовательность

Между созданием заявки и чтением результата должен существовать промежуток, управляемый планировщиком Joomla. Не следует удерживать HTTP-запрос пользователя в ожидании готовности.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Exception\TrackingException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();

try {
    $created = $client->getTickets(['80000000000001']);

    foreach ($created['tickets'] as $ticket) {
        // На практике ticket сохраняют в базе и проверяют следующей задачей планировщика.
        $result = $client->getOperationsByTicket($ticket);
        var_dump($result);
    }
} catch (TrackingException $exception) {
    throw new RuntimeException('Пакетное отслеживание завершилось ошибкой.', 0, $exception);
}
```
