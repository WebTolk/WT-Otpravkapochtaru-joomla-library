# Документация разработчика

## Назначение

`WT Otpravkapochtaru` - Joomla-библиотека для работы с API Почты России:

- REST API `otpravka-api.pochta.ru` для настроек аккаунта, заказов, партий, документов, тарифов и отделений;
- SOAP API трекинга Почты России для истории операций, наложенного платежа и пакетных запросов;
- системный Joomla-плагин как единая точка хранения учетных данных.

Основная публичная точка входа для прикладного кода - класс:

```php
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
```

Без аргументов клиент читает настройки из включенного Joomla-плагина `system/wt_otpravkapochtaru`. Для тестов или ручной конфигурации можно передать `CredentialsProvider`:

```php
use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru(new CredentialsProvider([
    'access_token' => '...',
    'auth_mode' => 'key',
    'user_key' => '...',
    'tracking_login' => '...',
    'tracking_password' => '...',
    'http_timeout' => 60,
]));
```

## Учетные данные

Класс `Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider` принимает `array`, `Joomla\Registry\Registry` или `null`.

Если передан `null`, параметры берутся из включенного системного плагина `System - WT Otpravkapochtaru`.

Публичные методы:

| Метод | Назначение |
| --- | --- |
| `__construct(array|Registry|null $params = null)` | Создает провайдер. При `null` параметры будут загружены из Joomla-плагина при первом обращении. |
| `getAccessToken(): string` | Возвращает `access_token`. Также поддерживает старое имя `AccessToken`. Бросает `ConfigurationException`, если значение пустое. |
| `getAuthMode(): string` | Возвращает режим авторизации: `key` или `login_password`. |
| `getUserKey(): string` | Возвращает пользовательский ключ для `X-User-Authorization` в режиме `key`. |
| `getUserLogin(): string` | Возвращает логин пользователя для режима `login_password`. |
| `getUserPassword(): string` | Возвращает пароль пользователя для режима `login_password`. |
| `getTrackingLogin(): string` | Возвращает SOAP-логин трекинга. |
| `getTrackingPassword(): string` | Возвращает SOAP-пароль трекинга. |
| `getHttpTimeout(): int` | Возвращает HTTP timeout, по умолчанию `60`. |
| `getUserAuthorizationHeader(): string` | Формирует значение для `X-User-Authorization`: ключ или base64 от `login:password`. |
| `params(): Registry` | Возвращает Joomla Registry с параметрами. При пустой конфигурации или выключенном плагине бросает `ConfigurationException`. |

## Основной фасад `Otpravkapochtaru`

Все методы фасада возвращают массив. Ошибки транспорта и бизнес-ошибки API преобразуются в исключения библиотеки.

Подробные практические примеры для каждого публичного метода фасада вынесены в [практический справочник методов фасада](facade-method-reference.md). В нем для каждого метода указано, что он делает, зачем нужен, пример вызова и типовая структура входных или выходных данных.

### Аккаунт и настройки

| Метод | Что делает |
| --- | --- |
| `getAccountInfo(): array` | Запрашивает `/1.0/settings`. Используется для проверки аккаунта и отображения данных в Joomla-поле `accountinfo`. |
| `getSettings(): array` | Синонимичный запрос к `/1.0/settings`. |
| `getShippingPoints(): array` | Возвращает пункты сдачи отправлений пользователя через `/1.0/user-shipping-points`. |
| `getApiLimit(): array` | Возвращает лимиты API через `/1.0/settings/limit`. |

### Заказы

| Метод | Что делает |
| --- | --- |
| `createOrders(array $orders): array` | Создает черновики отправлений через `PUT /1.0/user/backlog`. Элементы массива могут быть `Order` или массивами. |
| `editOrder(Order|array $order, int|string $id): array` | Обновляет черновик отправления `PUT /1.0/backlog/{id}`. |
| `findOrderById(int|string $id): array` | Получает отправление по внутреннему идентификатору через `GET /1.0/backlog/{id}`. |
| `findOrderByShopId(string $orderNumber): array` | Ищет отправление по номеру заказа магазина через `GET /1.0/backlog/search?query=...`. |
| `findOrderByRpo(string $rpo): array` | Ищет отправление по трек-номеру через `GET /1.0/shipment/search?query=...`. |
| `deleteOrders(array $orderIds): array` | Удаляет черновики через `DELETE /1.0/backlog`. |
| `returnOrdersToNew(array $orderIds): array` | Возвращает отправления в статус новых через `POST /1.0/user/backlog`. |

Пример создания заказа:

```php
use Webtolk\Otpravkapochtaru\Entity\Order;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();

$order = Order::fromArray([
    'order-num' => 'site-100500',
    'recipient-name' => 'Иванов Иван',
    'tel-address' => '+79990000000',
    'index-to' => '410012',
    'address-type-to' => 'DEFAULT',
    'region-to' => 'Саратовская область',
    'place-to' => 'Саратов',
    'street-to' => 'Московская',
    'house-to' => '1',
    'mail-type' => 'POSTAL_PARCEL',
    'mail-category' => 'ORDINARY',
    'mass' => 1000,
]);

$result = $client->createOrders([$order]);
```

### Надежность получателя

| Метод | Что делает |
| --- | --- |
| `getRecipientReliability(Recipient|array $recipient): array` | Проверяет одного получателя через `/1.0/unreliable-recipient`; из ответа-массива возвращает первый элемент, если он есть. |
| `getRecipientsReliability(array $recipients): array` | Проверяет список получателей через `/1.0/unreliable-recipient`. |

Пример:

```php
$result = $client->getRecipientReliability([
    'recipient-name' => 'Иванов Иван',
    'tel-address' => '+79990000000',
]);
```

### Партии и документы

| Метод | Что делает |
| --- | --- |
| `createBatch(array $orderIds, ?string $sendingDate = null, bool $useOnlineBalance = false): array` | Создает партию из идентификаторов отправлений через `POST /1.0/user/shipment`. При указанной дате добавляет query-параметры `sending-date` и `use-online-balance`. |
| `getAllBatches(?string $mailType = null, ?string $mailCategory = null, ?int $size = null, string $sort = 'ask', ?int $page = null): array` | Возвращает список партий через `GET /1.0/batch` с фильтрами и пагинацией. |
| `getOrdersInBatch(string $batchName, ?int $size = null, string $sort = 'ask', ?int $page = null): array` | Возвращает отправления партии через `GET /1.0/batch/{batchName}/shipment`. |
| `generateDocumentPackage(string $batchName, string $printType = 'paper', string $printTypeForm = 'one-sided'): array` | Загружает ZIP-комплект документов через `/1.0/forms/{batchName}/zip-all`. Возвращает `content`, `contentType`, `fileName`, `statusCode`, `headers`. |
| `generateDocumentF103(string $batchName): array` | Загружает форму Ф103 PDF через `/1.0/forms/{batchName}/f103pdf`. Возвращает бинарный ответ в той же структуре, что и `generateDocumentPackage()`. |

### Возвратные отправления

| Метод | Что делает |
| --- | --- |
| `createReturnShipment(string $directBarcode, string $mailType = 'UNDEFINED'): array` | Создает возвратное отправление по прямому трек-номеру через `PUT /1.0/returns`. |
| `createReturnShipments(array $returnShipments): array` | Создает возвраты без прямого отправления через `PUT /1.0/returns/return-without-direct`. Элементы могут быть `ReturnShipment` или массивами. |
| `editReturnShipment(ReturnShipment|array $returnShipment, string $rpo): array` | Редактирует возвратное отправление через `POST /1.0/returns/{rpo}`. |
| `deleteReturnShipment(string $rpo): array` | Удаляет отдельный возврат через `DELETE /1.0/returns/delete-separate-return?barcode=...`. |

### Тарифы

| Метод | Что делает |
| --- | --- |
| `getTariff(int|string $objectId, array $params, array $services = []): array` | Вызывает `/1.0/tariff`. В payload добавляет `object` и, если указан список услуг, `service` как строку через запятую. |
| `getTariffAndDeliveryPeriod(int|string $objectId, array $params, array $services = []): array` | Сейчас использует тот же endpoint и ту же логику, что `getTariff()`. |

Пример:

```php
$tariff = $client->getTariff(27030, [
    'from-index' => '410012',
    'to-index' => '455001',
    'mail-type' => 'POSTAL_PARCEL',
    'mail-category' => 'ORDINARY',
    'mass' => 1000,
]);
```

### Справочники и отделения

| Метод | Что делает |
| --- | --- |
| `getCountryList(): array` | Возвращает локальный справочник стран `CountryDictionary::all()`. Не делает HTTP-запрос. |
| `searchPostOfficeByIndex(int|string $postalCode, ?string $latitude = null, ?string $longitude = null, ?string $currentDateTime = null, bool $filterByOfficeType = true, bool $ufpsPostalCode = false): array` | Получает отделение по индексу через postoffice endpoint `/1.0/{postalCode}`. |
| `searchPostOfficeByAddress(string $address, int $count = 3): array` | Ищет отделения по адресу через `/1.0/by-address`. |
| `searchPostOfficeByCoordinates(array $params): array` | Ищет ближайшие отделения через `/1.0/nearby`. Если `filter` не задан, подставляет `ALL`. |
| `getPostOfficeServices(int|string $postalCode, ?string $serviceGroup = null): array` | Возвращает услуги отделения через `/1.0/{postalCode}/services` или `/1.0/{postalCode}/services/{serviceGroup}`. |
| `getPostalCodesInLocality(string $locality, string $region = '', string $district = ''): array` | Возвращает индексы населенного пункта через `/1.0/settlement.offices.codes`. |

### Трекинг

Эти методы используют SOAP API и отдельные `tracking_login` / `tracking_password`. Они не используют REST `access_token`.

| Метод | Что делает |
| --- | --- |
| `getOperationsByRpo(string $rpo, string $lang = 'RUS'): array` | Возвращает историю операций по одному РПО. |
| `getNpayInfo(string $rpo, string $lang = 'RUS'): array` | Возвращает события наложенного платежа по РПО. |
| `getTickets(array $rpoList, string $lang = 'RUS'): array` | Создает пакетные SOAP-заявки на трекинг. Разбивает список на чанки до `500` РПО. Возвращает `tickets` и `not_create`. |
| `getOperationsByTicket(string $ticket): array` | Получает результат пакетной заявки по ticket. Если данных нет, возвращает пустой массив. |

## Entity-классы

Entity-классы нужны для нормализации payload перед отправкой в API. Все они принимают массив через `fromArray()` и возвращают готовый массив через `toArray()`.

Ключи нормализуются: `camelCase` и `snake_case` преобразуются в формат API с дефисами и нижним регистром. Значения `null` удаляются из итогового payload.

| Класс | Публичные методы | Назначение и проверки |
| --- | --- | --- |
| `Order` | `fromArray(array $data): self`, `toArray(): array` | Отправление. Поддерживает `goods.items`, `customs-declaration`, `ecom-data`. По умолчанию задает `address-type-to=DEFAULT`, `fragile=false`, `mail-category=ORDINARY`, `mail-direct=643`, `mail-type=POSTAL_PARCEL`. Требует `index-to` или `str-index-to`. |
| `Item` | `fromArray(array $data): self`, `toArray(): array` | Товарная позиция. Нормализует ключи и удаляет `null`. |
| `Recipient` | `fromArray(array $data): self`, `toArray(): array` | Получатель для проверки надежности. Нормализует ключи и удаляет `null`. |
| `ReturnShipment` | `fromArray(array $data): self`, `toArray(): array` | Возвратное отправление. Требует `mail-type`, `recipient-name`, `sender-name`, `address-from`. |
| `AddressReturn` | `fromArray(array $data): self`, `toArray(): array` | Адрес возврата. По умолчанию задает `address-type=DEFAULT`. Требует `index`, `place`, `region`. |
| `CustomsDeclaration` | `fromArray(array $data): self`, `toArray(): array` | Таможенная декларация. Поддерживает `customs-entries`. По умолчанию задает `currency=RUB`, `entries-type=GIFT`. |
| `CustomsDeclarationItem` | `fromArray(array $data): self`, `toArray(): array` | Позиция таможенной декларации. |
| `EcomData` | `fromArray(array $data): self`, `toArray(): array` | Данные e-commerce блока. |

`AbstractEntity` содержит общий публичный контракт `toArray(): array`; создавать его напрямую нельзя.

Пример нормализации entity:

```php
use Webtolk\Otpravkapochtaru\Entity\Order;

$order = Order::fromArray([
    'orderNum' => 'site-100500',
    'index_to' => '410012',
    'recipient_name' => 'Иванов Иван',
    'tel_address' => '+79990000000',
    'mass' => 1000,
]);

$payload = $order->toArray();
```

Результирующая структура:

```php
[
    'order-num' => 'site-100500',
    'index-to' => '410012',
    'recipient-name' => 'Иванов Иван',
    'tel-address' => '+79990000000',
    'mass' => 1000,
    'address-type-to' => 'DEFAULT',
    'fragile' => false,
    'mail-category' => 'ORDINARY',
    'mail-direct' => 643,
    'mail-type' => 'POSTAL_PARCEL',
]
```

Пример структуры возвратного отправления:

```php
use Webtolk\Otpravkapochtaru\Entity\ReturnShipment;

$returnShipment = ReturnShipment::fromArray([
    'mail-type' => 'POSTAL_PARCEL',
    'recipient-name' => 'Иванов Иван',
    'sender-name' => 'ООО Ромашка',
    'address-from' => [
        'index' => '410012',
        'region' => 'Саратовская область',
        'place' => 'Саратов',
    ],
]);

$payload = $returnShipment->toArray();
```

Зачем использовать entity: они дают единое место нормализации ключей и раннюю проверку обязательных полей до HTTP-запроса.

## Низкоуровневые классы

Эти классы публичны технически, но в прикладном коде обычно достаточно фасада `Otpravkapochtaru`.

### `Request`

REST-клиент для API Почты России.

Константы endpoint:

- `Request::ENDPOINT_OTPRAVKA` - `https://otpravka-api.pochta.ru`;
- `Request::ENDPOINT_DELIVERY` - `https://delivery.pochta.ru/delivery`;
- `Request::ENDPOINT_POSTOFFICE` - `https://otpravka-api.pochta.ru/postoffice`.

Публичные методы:

| Метод | Назначение |
| --- | --- |
| `__construct(CredentialsProvider $credentialsProvider)` | Создает REST-клиент с провайдером учетных данных. |
| `get(string $path, array $query = [], string $endpoint = Request::ENDPOINT_OTPRAVKA): array` | GET JSON-запрос. |
| `postJson(string $path, array $payload, string $endpoint = Request::ENDPOINT_OTPRAVKA, array $query = []): array` | POST JSON-запрос. |
| `putJson(string $path, array $payload, string $endpoint = Request::ENDPOINT_OTPRAVKA): array` | PUT JSON-запрос. |
| `deleteJson(string $path, array $payload, string $endpoint = Request::ENDPOINT_OTPRAVKA): array` | DELETE JSON-запрос с JSON-body. |
| `delete(string $path, array $query = [], string $endpoint = Request::ENDPOINT_OTPRAVKA): array` | DELETE-запрос с query-параметрами. |
| `getBinary(string $path, array $query = [], string $endpoint = Request::ENDPOINT_OTPRAVKA): array` | GET бинарного ответа. Возвращает `content`, `contentType`, `fileName`, `statusCode`, `headers`. |

Пример прямого низкоуровневого запроса:

```php
use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Request;

$request = new Request(new CredentialsProvider());

$settings = $request->get('/1.0/settings');

$tariff = $request->postJson('/1.0/tariff', [
    'object' => '27030',
    'from-index' => '410012',
    'to-index' => '455001',
    'mass' => 1000,
]);
```

Зачем использовать напрямую: только когда фасад еще не содержит нужного endpoint. Для обычной интеграции предпочтительнее `Otpravkapochtaru`.

### `SoapRequest`

Фабрика SOAP-клиентов трекинга.

| Метод | Назначение |
| --- | --- |
| `__construct(CredentialsProvider $credentialsProvider)` | Создает фабрику SOAP-клиентов. |
| `createSingleClient(): SoapClient` | Создает SOAP 1.2 клиент одиночных запросов. |
| `createPackClient(): SoapClient` | Создает SOAP 1.1 клиент пакетных запросов. |
| `getTrackingLogin(): string` | Возвращает логин трекинга из провайдера. |
| `getTrackingPassword(): string` | Возвращает пароль трекинга из провайдера. |

Пример:

```php
use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\SoapRequest;

$soap = new SoapRequest(new CredentialsProvider());

$singleClient = $soap->createSingleClient();
$packClient = $soap->createPackClient();
```

Зачем использовать напрямую: для расширения или диагностики SOAP-трекинга. В прикладном коде обычно достаточно методов фасада `getOperationsByRpo()`, `getTickets()` и `getOperationsByTicket()`.

### `TrackingEntity`

Низкоуровневый SOAP-сервис трекинга. Его методы продублированы фасадом `Otpravkapochtaru`.

| Метод | Назначение |
| --- | --- |
| `__construct(SoapRequest $soapRequest)` | Создает сервис трекинга. |
| `getOperationsByRpo(string $rpo, string $lang = 'RUS'): array` | История операций по РПО. |
| `getNpayInfo(string $rpo, string $lang = 'RUS'): array` | События наложенного платежа. |
| `getTickets(array $rpoList, string $lang = 'RUS'): array` | Создание пакетных tickets. |
| `getOperationsByTicket(string $ticket): array` | Получение результата по ticket. |

Пример:

```php
use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\SoapRequest;
use Webtolk\Otpravkapochtaru\TrackingEntity;

$tracking = new TrackingEntity(new SoapRequest(new CredentialsProvider()));

$history = $tracking->getOperationsByRpo('80092123913448');
$tickets = $tracking->getTickets(['80092123913448']);
```

Зачем использовать напрямую: если нужен отдельный сервис трекинга без REST-фасада. Для большинства Joomla-интеграций лучше использовать `Otpravkapochtaru`.

### `CountryDictionary`

| Метод | Назначение |
| --- | --- |
| `all(): array` | Возвращает локальный справочник стран. |

Пример:

```php
use Webtolk\Otpravkapochtaru\Dictionaries\CountryDictionary;

$countries = CountryDictionary::all();
$russia = $countries[643] ?? null;
```

## Исключения

Все исключения библиотеки наследуются от `OtpravkapochtaruException`.

| Исключение | Когда возникает |
| --- | --- |
| `ConfigurationException` | Не хватает настроек: выключен плагин, пустой `access_token`, пустой `user_key`, `user_login` или `user_password`. |
| `TransportException` | HTTP-запрос вернул ошибку, не-JSON ответ, бизнес-ошибку API или неизвестный endpoint. |
| `TrackingException` | Ошибка SOAP-запроса трекинга. |
| `ValidationException` | Ошибка подготовки entity payload: не тот тип поля, нет обязательного поля. |

Рекомендуемый шаблон обработки:

```php
use Webtolk\Otpravkapochtaru\Exception\ConfigurationException;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Exception\TransportException;

try {
    $settings = $client->getAccountInfo();
} catch (ConfigurationException $e) {
    // Сообщить администратору, что плагин не настроен.
} catch (TransportException $e) {
    // Обработать ошибку API или сети.
} catch (OtpravkapochtaruException $e) {
    // Общий fallback для ошибок библиотеки.
}
```

## Joomla service provider

Плагин содержит service provider `plg_system_wt_otpravkapochtaru/services/provider.php`.

Публичный метод:

| Метод | Назначение |
| --- | --- |
| `register(Container $container): void` | Регистрирует расширение Joomla-плагина в DI container. Это инфраструктурный метод Joomla, в прикладном коде вызывать его не нужно. |
