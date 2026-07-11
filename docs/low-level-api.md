# Низкоуровневый публичный интерфейс

Прикладному коду рекомендуется использовать `Otpravkapochtaru`. Классы этой главы публичны для нестандартных маршрутов, тестирования и расширения интеграции. При прямом применении вызывающий код сам отвечает за форму данных и проверку ответа.

## `CredentialsProvider`

Провайдер принимает `array|Joomla\Registry\Registry|null`. При `null` он лениво читает параметры включённого плагина `system/wt_otpravkapochtaru`.

| Метод | Что делает | Зачем нужен | Как работает и что возвращает |
| --- | --- | --- | --- |
| `__construct(array\|Registry\|null $params = null)` | Создаёт провайдер. | Позволяет выбрать настройки Joomla или явные параметры теста. | Массив превращается в `Registry`; `null` откладывает чтение плагина. |
| `getAccessToken(): string` | Возвращает REST-токен. | Нужен заголовку `Authorization`. | Читает `access_token`, затем старое `AccessToken`; при пустом значении бросает `ConfigurationException`. |
| `getAuthMode(): string` | Возвращает режим авторизации пользователя. | Определяет форму второго заголовка авторизации. | Читает `auth_mode` или старое `user_key_or_login_and_password`; обычные значения — `key` и `login_password`. |
| `getUserKey(): string` | Возвращает пользовательский ключ. | Нужен режиму `key`. | Читает `user_key` или старое `user_auth_key`, не проверяя непустое значение. |
| `getUserLogin(): string` | Возвращает логин REST API. | Нужен режиму логин/пароль. | Читает `user_login`. |
| `getUserPassword(): string` | Возвращает пароль REST API. | Нужен режиму логин/пароль. | Читает `user_password`; значение нельзя записывать в журнал. |
| `getTrackingLogin(): string` | Возвращает SOAP-логин. | Нужен службе отслеживания. | Читает `tracking_login`. |
| `getTrackingPassword(): string` | Возвращает SOAP-пароль. | Нужен службе отслеживания. | Читает `tracking_password`; значение нельзя выводить. |
| `getHttpTimeout(): int` | Возвращает время ожидания. | Ограничивает REST- и SOAP-соединения. | Читает `http_timeout`, по умолчанию 60 секунд. |
| `getUserAuthorizationHeader(): string` | Формирует значение `X-User-Authorization`. | Централизует проверку второго набора реквизитов. | Для ключа возвращает ключ; для логина и пароля — base64 от `login:password`; пустые значения вызывают исключение. |
| `params(): Registry` | Возвращает все параметры. | Нужен редкий низкоуровневый доступ. | Возвращает кэшированный `Registry` либо загружает параметры включённого плагина. |

Пример с параметрами Joomla:

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Exception\ConfigurationException;

$credentials = new CredentialsProvider();

try {
    var_dump([
        'auth_mode' => $credentials->getAuthMode(),
        'has_access_token' => $credentials->getAccessToken() !== '',
        'has_user_key' => $credentials->getUserKey() !== '',
        'has_user_login' => $credentials->getUserLogin() !== '',
        'has_user_password' => $credentials->getUserPassword() !== '',
        'has_tracking_login' => $credentials->getTrackingLogin() !== '',
        'has_tracking_password' => $credentials->getTrackingPassword() !== '',
        'http_timeout' => $credentials->getHttpTimeout(),
        'has_user_authorization' => $credentials->getUserAuthorizationHeader() !== '',
        'parameter_names' => array_keys($credentials->params()->toArray()),
    ]);
} catch (ConfigurationException $exception) {
    throw new RuntimeException('Настройки библиотеки неполны.', 0, $exception);
}
```

Явная конфигурация подходит для автоматического теста, но секреты не следует хранить в исходном коде:

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;

$credentials = new CredentialsProvider([
    'access_token' => getenv('RUSSIAN_POST_ACCESS_TOKEN') ?: '',
    'auth_mode' => 'key',
    'user_key' => getenv('RUSSIAN_POST_USER_KEY') ?: '',
    'tracking_login' => getenv('RUSSIAN_POST_TRACKING_LOGIN') ?: '',
    'tracking_password' => getenv('RUSSIAN_POST_TRACKING_PASSWORD') ?: '',
    'http_timeout' => 60,
]);
```

## `Request`

Константы адресов:

| Константа | Базовый адрес |
| --- | --- |
| `Request::ENDPOINT_OTPRAVKA` | `https://otpravka-api.pochta.ru` |
| `Request::ENDPOINT_DELIVERY` | `https://delivery.pochta.ru/delivery` |
| `Request::ENDPOINT_POSTOFFICE` | `https://otpravka-api.pochta.ru/postoffice` |

Каждый JSON-метод возвращает `array<string, mixed>`. Коды HTTP от 400, не-JSON ответы и распознанные поля ошибок приводят к `TransportException`.

| Метод | Что делает | Зачем нужен | Как работает |
| --- | --- | --- | --- |
| `__construct(CredentialsProvider $credentialsProvider)` | Создаёт REST-транспорт. | Позволяет использовать те же параметры, что фасад. | Сохраняет провайдер для заголовков и времени ожидания. |
| `get(string $path, array $query = [], string $endpoint = ENDPOINT_OTPRAVKA): array` | Выполняет JSON GET. | Нужен для чтения нестандартного маршрута. | Удаляет `null` из запроса, переводит bool в строки, разбирает JSON. |
| `postJson(string $path, array $payload, string $endpoint = ENDPOINT_OTPRAVKA, array $query = []): array` | Выполняет JSON POST. | Нужен для нормализации, тарифа и командных операций. | Кодирует массив без экранирования русского текста; поддерживает параметры строки запроса. |
| `putJson(string $path, array $payload, string $endpoint = ENDPOINT_OTPRAVKA): array` | Выполняет JSON PUT. | Нужен для создания или замены ресурса. | Отправляет JSON-тело и разбирает ответ. |
| `deleteJson(string $path, array $payload, string $endpoint = ENDPOINT_OTPRAVKA): array` | Выполняет DELETE с JSON-телом. | Нужен маршрутам удаления списка идентификаторов. | Передаёт закодированный массив телом запроса. |
| `delete(string $path, array $query = [], string $endpoint = ENDPOINT_OTPRAVKA): array` | Выполняет DELETE с параметрами адреса. | Нужен удалению ресурса по РПО или другому ключу. | Формирует строку запроса и разбирает JSON. |
| `getBinary(string $path, array $query = [], string $endpoint = ENDPOINT_OTPRAVKA): array` | Загружает двоичный файл. | Нужен печатным документам. | Возвращает содержимое, тип, безопасное имя, код HTTP и заголовки. |

### `get()` и `postJson()`

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Exception\TransportException;
use Webtolk\Otpravkapochtaru\Request;

$request = new Request(new CredentialsProvider());

try {
    $settings = $request->get('/1.0/settings');
    $normalized = $request->postJson('/1.0/clean/address', [[
        'id' => 'address-1',
        'original-address' => '685000, Магадан',
    ]]);

    var_dump(['settings' => $settings, 'normalized' => $normalized]);
} catch (TransportException $exception) {
    throw new RuntimeException('Не удалось выполнить запрос чтения или нормализации.', 0, $exception);
}
```

### `putJson()` и `deleteJson()`

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Exception\TransportException;
use Webtolk\Otpravkapochtaru\Request;

$request = new Request(new CredentialsProvider());
$orderId = null;

try {
    $created = $request->putJson('/1.0/user/backlog', [[
        'order-num' => 'low-level-' . date('Ymd-His'),
        'recipient-name' => 'Иванов Иван Иванович',
        'tel-address' => '+79000000000',
        'index-to' => '685000',
        'mass' => 1000,
        'mail-type' => 'POSTAL_PARCEL',
        'mail-category' => 'ORDINARY',
    ]]);

    $orderId = $created['result-ids'][0] ?? null;

    if ($orderId === null) {
        throw new UnexpectedValueException('API не вернул идентификатор заказа.');
    }

    var_dump($created);
} catch (TransportException $exception) {
    throw new RuntimeException('Не удалось создать низкоуровневый заказ.', 0, $exception);
} finally {
    if ($orderId !== null) {
        $request->deleteJson('/1.0/backlog', [$orderId]);
    }
}
```

### `delete()`

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Exception\TransportException;
use Webtolk\Otpravkapochtaru\Request;

$request = new Request(new CredentialsProvider());
$rpo = Factory::getApplication()->getInput()->getString('return_rpo');

if ($rpo === '') {
    throw new InvalidArgumentException('Не передан РПО отдельного возврата.');
}

try {
    $result = $request->delete(
        '/1.0/returns/delete-separate-return',
        ['barcode' => $rpo],
    );

    var_dump($result);
} catch (TransportException $exception) {
    throw new RuntimeException('Не удалось удалить отдельный возврат.', 0, $exception);
}
```

### `getBinary()`

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Exception\TransportException;
use Webtolk\Otpravkapochtaru\Request;

$request = new Request(new CredentialsProvider());
$batchName = Factory::getApplication()->getInput()->getString('batch_name');

try {
    $document = $request->getBinary('/1.0/forms/' . $batchName . '/f103pdf');

    var_dump([
        'file_name' => $document['fileName'],
        'content_type' => $document['contentType'],
        'size' => strlen($document['content']),
    ]);
} catch (TransportException $exception) {
    throw new RuntimeException('Не удалось загрузить двоичный документ.', 0, $exception);
}
```

В обычном коде для операций с заказами следует использовать фасад и `Order`: сущность добавляет значения по умолчанию и проверяет обязательный индекс.

Двоичный результат:

```php
array{
    content: string,
    contentType: string,
    fileName: string|null,
    statusCode: int,
    headers: array<string, mixed>
}
```

## `SoapRequest`

| Метод | Что делает | Зачем нужен | Возвращаемый тип |
| --- | --- | --- | --- |
| `__construct(CredentialsProvider $credentialsProvider)` | Создаёт фабрику SOAP-клиентов. | Объединяет реквизиты и время ожидания. | `SoapRequest` |
| `createSingleClient(): SoapClient` | Создаёт SOAP 1.2 клиент `rtm34_wsdl.xml`. | Одиночная история и наложенный платёж. | `SoapClient` |
| `createPackClient(): SoapClient` | Создаёт SOAP 1.1 клиент `fc_wsdl.xml`. | Пакетные заявки. | `SoapClient` |
| `getTrackingLogin(): string` | Возвращает SOAP-логин. | Нужен ручной сборке запроса. | `string` |
| `getTrackingPassword(): string` | Возвращает SOAP-пароль. | Нужен ручной сборке запроса. | `string` |

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\SoapRequest;

$soap = new SoapRequest(new CredentialsProvider());

$singleClient = $soap->createSingleClient();
$packClient = $soap->createPackClient();

var_dump([
    'single_client' => $singleClient::class,
    'pack_client' => $packClient::class,
    'has_login' => $soap->getTrackingLogin() !== '',
    'has_password' => $soap->getTrackingPassword() !== '',
]);
```

## `TrackingEntity`

`TrackingEntity` является низкоуровневым SOAP-фасадом. Его четыре прикладных метода имеют те же аргументы и результаты, что одноимённые методы `Otpravkapochtaru`.

| Метод | Назначение | Подробное описание |
| --- | --- | --- |
| `__construct(SoapRequest $soapRequest)` | Создаёт службу отслеживания. | Получает фабрику SOAP-клиентов. |
| `getOperationsByRpo(string $rpo, string $lang = 'RUS'): array` | История одного РПО. | [Глава об отслеживании](api/tracking.md#getoperationsbyrpo). |
| `getNpayInfo(string $rpo, string $lang = 'RUS'): array` | События наложенного платежа. | [Глава об отслеживании](api/tracking.md#getnpayinfo). |
| `getTickets(array $rpoList, string $lang = 'RUS'): array` | Пакетные заявки по 500 РПО. | [Глава об отслеживании](api/tracking.md#gettickets). |
| `getOperationsByTicket(string $ticket): array` | Результат пакетной заявки. | [Глава об отслеживании](api/tracking.md#getoperationsbyticket). |

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\SoapRequest;
use Webtolk\Otpravkapochtaru\TrackingEntity;

$tracking = new TrackingEntity(
    new SoapRequest(new CredentialsProvider()),
);

$history = $tracking->getOperationsByRpo('80000000000001');
$npay = $tracking->getNpayInfo('80000000000001');
$tickets = $tracking->getTickets(['80000000000001']);

foreach ($tickets['tickets'] as $ticket) {
    $batchHistory = $tracking->getOperationsByTicket($ticket);
    var_dump($batchHistory);
}

var_dump(compact('history', 'npay', 'tickets'));
```

## `CountryDictionary::all(): array`

**Что делает.** Возвращает весь встроенный справочник стран.

**Зачем нужен.** Это прямой низкоуровневый эквивалент `Otpravkapochtaru::getCountryList()`.

**Как работает.** Возвращает статический массив `array<int, string>` и не выполняет сетевой запрос.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Dictionaries\CountryDictionary;

$countries = CountryDictionary::all();

var_dump($countries[643] ?? null);
```
