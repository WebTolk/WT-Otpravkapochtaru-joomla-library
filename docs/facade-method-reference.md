# Практический справочник методов фасада

Документ покрывает публичные методы `Webtolk\Otpravkapochtaru\Otpravkapochtaru`.

Общий шаблон:

```php
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
```

Если клиент создается без аргументов, учетные данные берутся из включенного Joomla-плагина `System - WT Otpravkapochtaru`.

Формат ответов зависит от API Почты России. Библиотека возвращает декодированный массив API без переименования полей ответа. В примерах ниже показаны типовые структуры, с которыми удобно работать в коде.

## Создание клиента

### `__construct(?CredentialsProvider $credentialsProvider = null)`

Что делает: создает фасад библиотеки и подготавливает REST-клиент и SOAP-клиент трекинга.

Зачем нужен: выбрать источник учетных данных. Если аргумент не передан, библиотека читает настройки из Joomla-плагина. Если передан `CredentialsProvider`, можно использовать явные настройки для тестов, CLI-скриптов или отдельной интеграции.

```php
use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

// Обычный Joomla-сценарий: настройки берутся из плагина.
$client = new Otpravkapochtaru();

// Явная конфигурация для теста или CLI-скрипта.
$client = new Otpravkapochtaru(new CredentialsProvider([
    'access_token' => 'REST_ACCESS_TOKEN',
    'auth_mode' => 'key',
    'user_key' => 'USER_AUTH_KEY',
    'tracking_login' => 'TRACKING_LOGIN',
    'tracking_password' => 'TRACKING_PASSWORD',
    'http_timeout' => 60,
]));
```

Структура входных настроек:

```php
[
    'access_token' => 'REST_ACCESS_TOKEN',
    'auth_mode' => 'key',
    'user_key' => 'USER_AUTH_KEY',
    'user_login' => '',
    'user_password' => '',
    'tracking_login' => 'TRACKING_LOGIN',
    'tracking_password' => 'TRACKING_PASSWORD',
    'http_timeout' => 60,
]
```

## Аккаунт и настройки

### `getAccountInfo(): array`

Что делает: запрашивает данные аккаунта через `/1.0/settings`.

Зачем нужен: проверить, что REST-доступ к API настроен корректно, и показать администратору данные организации.

```php
$account = $client->getAccountInfo();

$orgName = $account['org-name'] ?? '';
$apiEnabled = (int) ($account['api_enabled'] ?? 0) === 1;
```

Типовая структура:

```php
[
    'org-name' => 'ООО Ромашка',
    'org-inn' => '6450000000',
    'org-kpp' => '645001001',
    'agreement-number' => '123456',
    'agreement-date' => '2026-01-01',
    'espp-code' => '123456789',
    'api_enabled' => 1,
    'accounts' => [
        ['email' => 'mail@example.org'],
    ],
]
```

### `getSettings(): array`

Что делает: выполняет тот же запрос `/1.0/settings`, что и `getAccountInfo()`.

Зачем нужен: использовать более нейтральное имя метода, когда код работает именно с настройками API, а не с виджетом аккаунта.

```php
$settings = $client->getSettings();

if (($settings['sub-code'] ?? '') === 'UNAUTHORIZED') {
    throw new RuntimeException('Почта России отклонила учетные данные.');
}
```

Типовая структура: такая же, как у `getAccountInfo()`.

### `getShippingPoints(): array`

Что делает: получает пункты сдачи отправлений пользователя через `/1.0/user-shipping-points`.

Зачем нужен: заполнить список доступных мест сдачи отправлений в настройках доставки или в административной форме.

```php
$points = $client->getShippingPoints();

foreach ($points as $point) {
    $index = $point['operator-postcode'] ?? null;
    $name = $point['ops-name'] ?? null;
}
```

Типовая структура:

```php
[
    [
        'operator-postcode' => '410012',
        'ops-name' => 'САРАТОВ 12',
        'address' => 'г Саратов, ул ...',
    ],
]
```

### `getApiLimit(): array`

Что делает: получает лимит запросов через `/1.0/settings/limit`.

Зачем нужен: показать администратору остаток API-запросов и заранее диагностировать ограничение аккаунта.

```php
$limit = $client->getApiLimit();

$allowed = (int) ($limit['allowed-count'] ?? 0);
$used = (int) ($limit['current-count'] ?? 0);
$remaining = max(0, $allowed - $used);
```

Типовая структура:

```php
[
    'allowed-count' => 10000,
    'current-count' => 125,
]
```

## Заказы

### `createOrders(array $orders): array`

Что делает: создает один или несколько черновиков отправлений через `PUT /1.0/user/backlog`.

Зачем нужен: передать заказы магазина в личный кабинет Почты России для дальнейшей обработки и формирования партии.

```php
use Webtolk\Otpravkapochtaru\Entity\Order;

$orders = [
    Order::fromArray([
        'order-num' => 'site-100500',
        'recipient-name' => 'Иванов Иван',
        'tel-address' => '+79990000000',
        'index-to' => '410012',
        'region-to' => 'Саратовская область',
        'place-to' => 'Саратов',
        'street-to' => 'Московская',
        'house-to' => '1',
        'mail-type' => 'POSTAL_PARCEL',
        'mail-category' => 'ORDINARY',
        'mass' => 1000,
        'goods' => [
            'items' => [
                [
                    'description' => 'Товар',
                    'quantity' => 1,
                    'value' => 150000,
                ],
            ],
        ],
    ]),
];

$created = $client->createOrders($orders);
```

Структура входа:

```php
[
    [
        'order-num' => 'site-100500',
        'recipient-name' => 'Иванов Иван',
        'tel-address' => '+79990000000',
        'index-to' => '410012',
        'mail-type' => 'POSTAL_PARCEL',
        'mail-category' => 'ORDINARY',
        'mass' => 1000,
    ],
]
```

Типовая структура ответа:

```php
[
    [
        'id' => 2315788012,
        'order-num' => 'site-100500',
        'barcode' => '80092123913448',
    ],
]
```

### `editOrder(Order|array $order, int|string $id): array`

Что делает: обновляет черновик отправления по идентификатору через `PUT /1.0/backlog/{id}`.

Зачем нужен: синхронизировать изменения заказа Joomla с уже созданным черновиком в Почте России.

```php
$updated = $client->editOrder([
    'recipient-name' => 'Петров Петр',
    'tel-address' => '+79991112233',
    'index-to' => '410012',
    'mass' => 1200,
], 2315788012);
```

Структура входа: такой же payload отправления, как у `createOrders()`, но для одного заказа.

Типовая структура ответа:

```php
[
    'id' => 2315788012,
    'order-num' => 'site-100500',
    'barcode' => '80092123913448',
]
```

### `findOrderById(int|string $id): array`

Что делает: получает отправление по внутреннему идентификатору Почты России.

Зачем нужен: проверить состояние конкретного отправления после создания или обновления.

```php
$order = $client->findOrderById(2315788012);
```

Типовая структура ответа:

```php
[
    'id' => 2315788012,
    'order-num' => 'site-100500',
    'barcode' => '80092123913448',
    'mail-type' => 'POSTAL_PARCEL',
]
```

### `findOrderByShopId(string $orderNumber): array`

Что делает: ищет отправление по номеру заказа магазина.

Зачем нужен: восстановить связь между заказом Joomla и отправлением Почты России без хранения внутреннего id.

```php
$order = $client->findOrderByShopId('site-100500');
```

Типовая структура ответа:

```php
[
    [
        'id' => 2315788012,
        'order-num' => 'site-100500',
        'barcode' => '80092123913448',
    ],
]
```

### `findOrderByRpo(string $rpo): array`

Что делает: ищет отправление по трек-номеру РПО.

Зачем нужен: получить данные отправления, когда в системе есть только barcode.

```php
$shipment = $client->findOrderByRpo('80092123913448');
```

Типовая структура ответа:

```php
[
    [
        'id' => 2315788012,
        'barcode' => '80092123913448',
        'order-num' => 'site-100500',
    ],
]
```

### `deleteOrders(array $orderIds): array`

Что делает: удаляет черновики отправлений.

Зачем нужен: отменить передачу ошибочно созданных или больше не актуальных отправлений.

```php
$result = $client->deleteOrders([2315788012, 2315788013]);
```

Структура входа:

```php
[2315788012, 2315788013]
```

Типовая структура ответа:

```php
[
    'result' => 'success',
]
```

### `returnOrdersToNew(array $orderIds): array`

Что делает: возвращает отправления в статус новых.

Зачем нужен: снять отправления с дальнейшего этапа обработки и вернуть их к редактируемому состоянию.

```php
$result = $client->returnOrdersToNew([2315788012]);
```

Структура входа:

```php
[2315788012]
```

## Надежность получателя

### `getRecipientReliability(Recipient|array $recipient): array`

Что делает: проверяет одного получателя через `/1.0/unreliable-recipient`.

Зачем нужен: предупредить менеджера о потенциально проблемном получателе до создания отправления.

```php
$reliability = $client->getRecipientReliability([
    'recipient-name' => 'Иванов Иван',
    'tel-address' => '+79990000000',
]);
```

Структура входа:

```php
[
    'recipient-name' => 'Иванов Иван',
    'tel-address' => '+79990000000',
]
```

Типовая структура ответа:

```php
[
    'recipient-name' => 'Иванов Иван',
    'reliability' => 'NORMAL',
]
```

### `getRecipientsReliability(array $recipients): array`

Что делает: проверяет список получателей.

Зачем нужен: массово проверить получателей перед пакетной выгрузкой заказов.

```php
$result = $client->getRecipientsReliability([
    ['recipient-name' => 'Иванов Иван', 'tel-address' => '+79990000000'],
    ['recipient-name' => 'Петров Петр', 'tel-address' => '+79991112233'],
]);
```

Структура входа:

```php
[
    ['recipient-name' => 'Иванов Иван', 'tel-address' => '+79990000000'],
    ['recipient-name' => 'Петров Петр', 'tel-address' => '+79991112233'],
]
```

## Партии и документы

### `createBatch(array $orderIds, ?string $sendingDate = null, bool $useOnlineBalance = false): array`

Что делает: формирует партию из отправлений.

Зачем нужен: подготовить отправления к сдаче и печати сопроводительных документов.

```php
$batch = $client->createBatch([2315788012], '2026-07-10', true);
```

Структура входа:

```php
[
    2315788012,
]
```

Типовая структура ответа:

```php
[
    'batch-name' => '1234-5678-9012',
    'shipment-count' => 1,
]
```

### `getAllBatches(?string $mailType = null, ?string $mailCategory = null, ?int $size = null, string $sort = 'ask', ?int $page = null): array`

Что делает: получает список партий с фильтрами.

Зачем нужен: показать менеджеру архив партий или найти партию для печати документов.

```php
$batches = $client->getAllBatches('POSTAL_PARCEL', 'ORDINARY', 20, 'ask', 0);
```

Типовая структура ответа:

```php
[
    'content' => [
        [
            'batch-name' => '1234-5678-9012',
            'mail-type' => 'POSTAL_PARCEL',
            'mail-category' => 'ORDINARY',
        ],
    ],
    'page' => 0,
    'size' => 20,
]
```

### `getOrdersInBatch(string $batchName, ?int $size = null, string $sort = 'ask', ?int $page = null): array`

Что делает: получает отправления внутри партии.

Зачем нужен: показать состав партии и проверить, попал ли заказ в нужную партию.

```php
$orders = $client->getOrdersInBatch('1234-5678-9012', 50, 'ask', 0);
```

Типовая структура ответа:

```php
[
    'content' => [
        [
            'id' => 2315788012,
            'barcode' => '80092123913448',
            'order-num' => 'site-100500',
        ],
    ],
]
```

### `generateDocumentPackage(string $batchName, string $printType = 'paper', string $printTypeForm = 'one-sided'): array`

Что делает: загружает ZIP-комплект документов партии.

Зачем нужен: дать менеджеру готовый архив печатных форм для сдачи партии.

```php
$document = $client->generateDocumentPackage('1234-5678-9012');

file_put_contents(
    JPATH_ROOT . '/tmp/' . ($document['fileName'] ?? 'documents.zip'),
    $document['content']
);
```

Структура ответа:

```php
[
    'content' => 'binary zip content',
    'contentType' => 'application/zip',
    'fileName' => 'forms.zip',
    'statusCode' => 200,
    'headers' => [],
]
```

### `generateDocumentF103(string $batchName): array`

Что делает: загружает форму Ф103 в PDF.

Зачем нужен: получить основную форму списка отправлений партии.

```php
$f103 = $client->generateDocumentF103('1234-5678-9012');

file_put_contents(JPATH_ROOT . '/tmp/f103.pdf', $f103['content']);
```

Структура ответа такая же, как у `generateDocumentPackage()`, но `contentType` обычно PDF.

## Возвратные отправления

### `createReturnShipment(string $directBarcode, string $mailType = 'UNDEFINED'): array`

Что делает: создает возвратное отправление на основе прямого barcode.

Зачем нужен: оформить возврат для уже существующего отправления.

```php
$return = $client->createReturnShipment('80092123913448', 'POSTAL_PARCEL');
```

Типовая структура ответа:

```php
[
    'barcode' => '80092123913455',
    'direct-barcode' => '80092123913448',
]
```

### `createReturnShipments(array $returnShipments): array`

Что делает: создает возвраты без прямого отправления.

Зачем нужен: оформить возвратную логистику, когда прямое отправление не создавалось через этот аккаунт.

```php
use Webtolk\Otpravkapochtaru\Entity\ReturnShipment;

$result = $client->createReturnShipments([
    ReturnShipment::fromArray([
        'mail-type' => 'POSTAL_PARCEL',
        'recipient-name' => 'Иванов Иван',
        'sender-name' => 'ООО Ромашка',
        'address-from' => [
            'index' => '410012',
            'region' => 'Саратовская область',
            'place' => 'Саратов',
        ],
    ]),
]);
```

Структура входа:

```php
[
    [
        'mail-type' => 'POSTAL_PARCEL',
        'recipient-name' => 'Иванов Иван',
        'sender-name' => 'ООО Ромашка',
        'address-from' => [
            'index' => '410012',
            'region' => 'Саратовская область',
            'place' => 'Саратов',
        ],
    ],
]
```

### `editReturnShipment(ReturnShipment|array $returnShipment, string $rpo): array`

Что делает: редактирует возвратное отправление по РПО.

Зачем нужен: изменить данные возврата до финальной обработки.

```php
$result = $client->editReturnShipment([
    'mail-type' => 'POSTAL_PARCEL',
    'recipient-name' => 'Петров Петр',
    'sender-name' => 'ООО Ромашка',
    'address-from' => [
        'index' => '410012',
        'region' => 'Саратовская область',
        'place' => 'Саратов',
    ],
], '80092123913455');
```

### `deleteReturnShipment(string $rpo): array`

Что делает: удаляет возвратное отправление.

Зачем нужен: отменить ошибочно созданный возврат.

```php
$result = $client->deleteReturnShipment('80092123913455');
```

## Тарифы

### `getTariff(int|string $objectId, array $params, array $services = []): array`

Что делает: рассчитывает тариф через `/1.0/tariff`.

Зачем нужен: показать стоимость доставки в корзине, заказе или административной форме.

```php
$tariff = $client->getTariff(27030, [
    'from-index' => '410012',
    'to-index' => '455001',
    'mail-type' => 'POSTAL_PARCEL',
    'mail-category' => 'ORDINARY',
    'mass' => 1000,
], [2, 15]);
```

Структура входа:

```php
[
    'object' => '27030',
    'from-index' => '410012',
    'to-index' => '455001',
    'mail-type' => 'POSTAL_PARCEL',
    'mail-category' => 'ORDINARY',
    'mass' => 1000,
    'service' => '2,15',
]
```

Типовая структура ответа:

```php
[
    'total-rate' => 45000,
    'total-vat' => 9000,
    'delivery-time' => [
        'min-days' => 3,
        'max-days' => 5,
    ],
]
```

### `getTariffAndDeliveryPeriod(int|string $objectId, array $params, array $services = []): array`

Что делает: вызывает тот же `/1.0/tariff` с тем же payload, что `getTariff()`.

Зачем нужен: использовать имя метода, явно указывающее, что вызывающий код ожидает не только стоимость, но и срок доставки, если API вернет его в ответе.

```php
$tariff = $client->getTariffAndDeliveryPeriod(27030, [
    'from-index' => '410012',
    'to-index' => '455001',
    'mass' => 1000,
]);

$period = $tariff['delivery-time'] ?? null;
```

## Справочники и отделения

### `getCountryList(): array`

Что делает: возвращает локальный справочник стран.

Зачем нужен: заполнить список стран без HTTP-запроса к Почте России.

```php
$countries = $client->getCountryList();

$russia = $countries[643] ?? null;
```

Типовая структура:

```php
[
    643 => 'Россия',
    112 => 'Беларусь',
]
```

### `searchPostOfficeByIndex(...)`

Что делает: получает данные отделения по индексу.

Зачем нужен: проверить существование индекса и получить данные отделения при оформлении отправления.

```php
$office = $client->searchPostOfficeByIndex(
    postalCode: '410012',
    latitude: null,
    longitude: null,
    currentDateTime: date('c'),
    filterByOfficeType: true,
    ufpsPostalCode: false,
);
```

Типовая структура ответа:

```php
[
    'postal-code' => '410012',
    'address-source' => 'г Саратов, ...',
    'type-code' => 'ГОПС',
]
```

### `searchPostOfficeByAddress(string $address, int $count = 3): array`

Что делает: ищет отделения по текстовому адресу.

Зачем нужен: подобрать ближайшее отделение, когда известен адрес, но неизвестен индекс.

```php
$offices = $client->searchPostOfficeByAddress('Саратов, Московская 1', 5);
```

Типовая структура ответа:

```php
[
    [
        'postal-code' => '410012',
        'address-source' => 'г Саратов, ...',
    ],
]
```

### `searchPostOfficeByCoordinates(array $params): array`

Что делает: ищет отделения рядом с координатами.

Зачем нужен: подобрать ближайшие отделения на карте или в checkout.

```php
$offices = $client->searchPostOfficeByCoordinates([
    'latitude' => '51.5336',
    'longitude' => '46.0343',
    'top' => 10,
    'filter' => 'ALL',
]);
```

Структура входа:

```php
[
    'latitude' => '51.5336',
    'longitude' => '46.0343',
    'top' => 10,
    'filter' => 'ALL',
]
```

### `getPostOfficeServices(int|string $postalCode, ?string $serviceGroup = null): array`

Что делает: получает услуги отделения.

Зачем нужен: понять, поддерживает ли отделение нужные операции.

```php
$allServices = $client->getPostOfficeServices('410012');
$groupServices = $client->getPostOfficeServices('410012', 'POSTAL');
```

Типовая структура ответа:

```php
[
    [
        'code' => 'POSTAL',
        'name' => 'Почтовые услуги',
    ],
]
```

### `getPostalCodesInLocality(string $locality, string $region = '', string $district = ''): array`

Что делает: получает индексы населенного пункта.

Зачем нужен: подсказать доступные индексы при неполном адресе.

```php
$codes = $client->getPostalCodesInLocality('Саратов', 'Саратовская область');
```

Типовая структура ответа:

```php
[
    '410000',
    '410012',
]
```

## Трекинг

### `getOperationsByRpo(string $rpo, string $lang = 'RUS'): array`

Что делает: получает историю операций по одному РПО через SOAP.

Зачем нужен: показать статус доставки в заказе Joomla.

```php
$history = $client->getOperationsByRpo('80092123913448');
```

Типовая структура ответа:

```php
[
    [
        'OperationParameters' => [
            'OperType' => ['Name' => 'Прием'],
            'OperDate' => '2026-07-08T10:00:00',
        ],
        'AddressParameters' => [
            'OperationAddress' => ['Description' => 'Саратов'],
        ],
    ],
]
```

### `getNpayInfo(string $rpo, string $lang = 'RUS'): array`

Что делает: получает события наложенного платежа по РПО через SOAP.

Зачем нужен: отследить движение наложенного платежа отдельно от логистического статуса.

```php
$npay = $client->getNpayInfo('80092123913448');
```

Типовая структура ответа:

```php
[
    [
        'EventName' => 'Оплата',
        'EventDate' => '2026-07-12T12:00:00',
        'Amount' => 150000,
    ],
]
```

### `getTickets(array $rpoList, string $lang = 'RUS'): array`

Что делает: создает пакетные заявки на получение трекинга.

Зачем нужен: эффективно запросить историю по большому списку РПО без одиночного SOAP-запроса на каждый номер.

```php
$tickets = $client->getTickets([
    '80092123913448',
    '80092123913455',
]);
```

Структура ответа:

```php
[
    'tickets' => [
        'ticket-uuid-or-number',
    ],
    'not_create' => [
        '80092123913455',
    ],
]
```

### `getOperationsByTicket(string $ticket): array`

Что делает: получает результат пакетной заявки по ticket.

Зачем нужен: забрать готовые результаты после `getTickets()`.

```php
$tickets = $client->getTickets(['80092123913448']);

foreach ($tickets['tickets'] as $ticket) {
    $items = $client->getOperationsByTicket($ticket);
}
```

Типовая структура ответа:

```php
[
    [
        'Barcode' => '80092123913448',
        'OperationHistoryData' => [
            'historyRecord' => [],
        ],
    ],
]
```
