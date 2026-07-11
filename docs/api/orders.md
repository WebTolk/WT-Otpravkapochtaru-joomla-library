# Заказы и проверка получателя

Черновик отправления проходит несколько состояний. Пока он находится в очереди новых заказов, его можно изменять и удалять. После включения в партию для редактирования или удаления его сначала следует вернуть в состояние `NEW` методом `returnOrdersToNew()`.

Библиотека принимает и массивы, и сущности `Order`/`Recipient`. Фасад в любом случае создаёт сущность, нормализует имена ключей и проверяет обязательные поля перед сетевым запросом.

<a id="createorders"></a>
## `createOrders(array $orders): array`

**Что делает.** Создаёт один или несколько черновиков отправлений.

**Зачем нужен.** Метод переносит подготовленные заказы Joomla в очередь отправки Почты России и возвращает их внутренние идентификаторы.

**Как работает.** Каждый элемент преобразуется через `Order::fromArray()->toArray()`, после чего список отправляется запросом `PUT /1.0/user/backlog`.

| Параметр | Тип | Обязателен | Назначение |
| --- | --- | --- | --- |
| `$orders` | `list<Order\|array<string, mixed>>` | да | Один или несколько заказов. |

Минимально сущность требует `index-to` или `str-index-to`. Значения `address-type-to=DEFAULT`, `fragile=false`, `mail-category=ORDINARY`, `mail-direct=643` и `mail-type=POSTAL_PARCEL` добавляются по умолчанию.

Наблюдаемые данные: [пример ответа](../api-schemas/otpravka/examples/create-orders.response.json) и [JSON Schema](../api-schemas/otpravka/schemas/create-orders.response.schema.json).

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Entity\Order;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;
use Webtolk\Otpravkapochtaru\Request;

$credentials = new CredentialsProvider();
$request = new Request($credentials);
$client = new Otpravkapochtaru($credentials);

try {
    $addressResult = $request->postJson('/1.0/clean/address', [[
        'id' => 'recipient-address',
        'original-address' => '685000, Магадан, проспект Ленина, дом 1',
    ]]);
    $personResult = $request->postJson('/1.0/clean/physical', [[
        'id' => 'recipient-name',
        'original-fio' => 'Иванов Иван Иванович',
    ]]);
    $phoneResult = $request->postJson('/1.0/clean/phone', [[
        'id' => 'recipient-phone',
        'original-phone' => '+7 900 000-00-00',
    ]]);

    $address = $addressResult[0] ?? [];
    $person = $personResult[0] ?? [];
    $phone = $phoneResult[0] ?? [];

    $recipientName = trim(implode(' ', array_filter([
        $person['surname'] ?? null,
        $person['name'] ?? null,
        $person['middle-name'] ?? null,
    ])));
    $recipientPhone = '+' . implode('', [
        $phone['phone-country-code'] ?? '',
        $phone['phone-city-code'] ?? '',
        $phone['phone-number'] ?? '',
    ]);

    if (empty($address['index']) || $recipientName === '' || $recipientPhone === '+') {
        throw new UnexpectedValueException('Нормализация не вернула обязательные данные.');
    }

    $order = Order::fromArray([
        'order-num' => 'joomla-' . date('Ymd-His'),
        'recipient-name' => $recipientName,
        'tel-address' => $recipientPhone,
        'index-to' => (string) $address['index'],
        'region-to' => $address['region'] ?? null,
        'place-to' => $address['place'] ?? null,
        'street-to' => $address['street'] ?? null,
        'house-to' => $address['house'] ?? null,
        'mail-type' => 'POSTAL_PARCEL',
        'mail-category' => 'ORDINARY',
        'mass' => 1000,
    ]);

    $created = $client->createOrders([$order]);
    $orderId = $created['result-ids'][0] ?? null;

    var_dump(['order_id' => $orderId, 'response' => $created]);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось создать отправление.', 0, $exception);
}
```

<a id="editorder"></a>
## `editOrder(Order|array $order, int|string $id): array`

**Что делает.** Полностью передаёт обновлённые данные существующего черновика.

**Зачем нужен.** Метод синхронизирует изменения адреса, получателя, массы и других параметров после редактирования заказа в Joomla.

**Как работает.** Данные проходят ту же нормализацию `Order`, затем выполняется `PUT /1.0/backlog/{id}`. В наблюдаемом прогоне успешный ответ был пустым массивом, поэтому итог следует проверять повторным чтением заказа.

| Параметр | Тип | Обязателен | Назначение |
| --- | --- | --- | --- |
| `$order` | `Order\|array<string, mixed>` | да | Полное новое состояние заказа. |
| `$id` | `int\|string` | да | Внутренний идентификатор Почты России. |

Наблюдаемые данные: [пример ответа](../api-schemas/otpravka/examples/edit-order.response.json) и [JSON Schema](../api-schemas/otpravka/schemas/edit-order.response.schema.json).

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Entity\Order;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$orderId = Factory::getApplication()->getInput()->getInt('russian_post_order_id');

if ($orderId <= 0) {
    throw new InvalidArgumentException('Не передан идентификатор отправления.');
}

try {
    $updatedOrder = Order::fromArray([
        'order-num' => 'joomla-order-1042-updated',
        'recipient-name' => 'Иванов Иван Иванович',
        'tel-address' => '+79000000000',
        'index-to' => '685000',
        'region-to' => 'Магаданская область',
        'place-to' => 'Магадан',
        'street-to' => 'проспект Ленина',
        'house-to' => '1',
        'mail-type' => 'POSTAL_PARCEL',
        'mail-category' => 'ORDINARY',
        'mass' => 1200,
    ]);

    $client->editOrder($updatedOrder, $orderId);
    $actual = $client->findOrderById($orderId);

    var_dump($actual);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось изменить отправление.', 0, $exception);
}
```

<a id="findorderbyid"></a>
## `findOrderById(int|string $id): array`

**Что делает.** Получает заказ по внутреннему идентификатору Почты России.

**Зачем нужен.** Это наиболее точный способ проверить фактические данные после создания или изменения.

**Как работает.** Выполняет `GET /1.0/backlog/{id}`.

Наблюдаемые данные: [пример ответа](../api-schemas/otpravka/examples/find-order-by-id.response.json) и [JSON Schema](../api-schemas/otpravka/schemas/find-order-by-id.response.schema.json).

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$orderId = Factory::getApplication()->getInput()->getInt('russian_post_order_id');

try {
    $order = $client->findOrderById($orderId);

    var_dump([
        'id' => $order['id'] ?? null,
        'order_number' => $order['order-num'] ?? null,
        'barcode' => $order['barcode'] ?? null,
        'mass' => $order['mass'] ?? null,
    ]);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось получить отправление.', 0, $exception);
}
```

<a id="findorderbyshopid"></a>
## `findOrderByShopId(string $orderNumber): array`

**Что делает.** Ищет заказы по значению `order-num`.

**Зачем нужен.** Поиск позволяет восстановить связь с отправлением, если приложение сохранило номер заказа магазина, но не сохранило внутренний идентификатор Почты России.

**Как работает.** Выполняет `GET /1.0/backlog/search?query={orderNumber}`. Возвращается список совпадений.

| Параметр | Тип | Назначение |
| --- | --- | --- |
| `$orderNumber` | `string` | Номер заказа, ранее переданный в `order-num`. |

Наблюдаемые данные: [пример ответа](../api-schemas/otpravka/examples/find-order-by-shop-id.response.json) и [JSON Schema](../api-schemas/otpravka/schemas/find-order-by-shop-id.response.schema.json).

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$orderNumber = Factory::getApplication()->getInput()->getString('order_number');

try {
    $matches = $client->findOrderByShopId($orderNumber);
    $first = $matches[0] ?? null;

    var_dump($first);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось найти отправление по номеру заказа.', 0, $exception);
}
```

<a id="findorderbyrpo"></a>
## `findOrderByRpo(string $rpo): array`

**Что делает.** Ищет оформленное отправление по почтовому идентификатору РПО.

**Зачем нужен.** Метод связывает штрихкод на печатной форме с записью заказа и его параметрами.

**Как работает.** Выполняет `GET /1.0/shipment/search?query={rpo}`.

Наблюдаемые данные: [пример ответа](../api-schemas/otpravka/examples/find-order-by-rpo.response.json) и [JSON Schema](../api-schemas/otpravka/schemas/find-order-by-rpo.response.schema.json).

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$rpo = preg_replace('/\s+/', '', Factory::getApplication()->getInput()->getString('rpo')) ?? '';

try {
    $shipments = $client->findOrderByRpo($rpo);

    var_dump($shipments[0] ?? null);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось найти отправление по РПО.', 0, $exception);
}
```

<a id="deleteorders"></a>
## `deleteOrders(array $orderIds): array`

**Что делает.** Удаляет черновики из очереди новых заказов.

**Зачем нужен.** Операция отменяет ошибочную или утратившую актуальность подготовку отправления.

**Как работает.** Передаёт список идентификаторов телом `DELETE /1.0/backlog`. Для заказа в партии сначала вызывается `returnOrdersToNew()`.

Наблюдаемые данные: [пример ответа](../api-schemas/otpravka/examples/delete-orders.response.json) и [JSON Schema](../api-schemas/otpravka/schemas/delete-orders.response.schema.json).

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$orderId = Factory::getApplication()->getInput()->getInt('russian_post_order_id');

try {
    $client->returnOrdersToNew([$orderId]);
    $deleted = $client->deleteOrders([$orderId]);

    var_dump([
        'deleted_ids' => $deleted['result-ids'] ?? [],
        'response' => $deleted,
    ]);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось удалить отправление.', 0, $exception);
}
```

<a id="returnorderstonew"></a>
## `returnOrdersToNew(array $orderIds): array`

**Что делает.** Возвращает заказы из партии в редактируемое состояние новых заказов.

**Зачем нужен.** Это обязательный подготовительный шаг перед изменением или удалением уже включённого в партию заказа.

**Как работает.** Отправляет список идентификаторов запросом `POST /1.0/user/backlog`.

Наблюдаемые данные: [пример ответа](../api-schemas/otpravka/examples/return-orders-to-new.response.json) и [JSON Schema](../api-schemas/otpravka/schemas/return-orders-to-new.response.schema.json).

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$orderId = Factory::getApplication()->getInput()->getInt('russian_post_order_id');

try {
    $result = $client->returnOrdersToNew([$orderId]);
    $order = $client->findOrderById($orderId);

    var_dump(['result' => $result, 'order' => $order]);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось вернуть отправление в очередь новых.', 0, $exception);
}
```

<a id="getrecipientreliability"></a>
## `getRecipientReliability(Recipient|array $recipient): array`

**Что делает.** Проверяет одного получателя на признаки неблагонадёжности.

**Зачем нужен.** Проверка до создания отправления позволяет предупредить менеджера или выбрать более безопасный способ оплаты.

**Как работает.** Сущность преобразует псевдонимы `address`, `name`, `phone` в поля `raw-address`, `raw-full-name`, `raw-telephone`. API принимает список, поэтому фасад оборачивает получателя в массив и возвращает первый элемент ответа.

Наблюдаемые данные: [пример ответа](../api-schemas/otpravka/examples/get-recipient-reliability.response.json) и [JSON Schema](../api-schemas/otpravka/schemas/get-recipient-reliability.response.schema.json).

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Entity\Recipient;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$recipient = Recipient::fromArray([
    'address' => '685000, Магадан, проспект Ленина, дом 1',
    'name' => 'Иванов Иван Иванович',
    'phone' => '+79000000000',
]);

try {
    $result = $client->getRecipientReliability($recipient);

    var_dump([
        'unreliability' => $result['unreliability'] ?? null,
        'response' => $result,
    ]);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось проверить получателя.', 0, $exception);
}
```

<a id="getrecipientsreliability"></a>
## `getRecipientsReliability(array $recipients): array`

**Что делает.** Проверяет список получателей одним запросом.

**Зачем нужен.** Пакетная проверка сокращает число сетевых обращений перед массовой выгрузкой заказов.

**Как работает.** Каждый элемент нормализуется через `Recipient`, после чего весь список отправляется в `POST /1.0/unreliable-recipient`.

Наблюдаемые данные: [пример ответа](../api-schemas/otpravka/examples/get-recipients-reliability.response.json) и [JSON Schema](../api-schemas/otpravka/schemas/get-recipients-reliability.response.schema.json).

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Entity\Recipient;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$recipients = [
    Recipient::fromArray([
        'address' => '685000, Магадан, проспект Ленина, дом 1',
        'name' => 'Иванов Иван Иванович',
        'phone' => '+79000000000',
    ]),
    Recipient::fromArray([
        'address' => '410000, Саратов, улица Московская, дом 1',
        'name' => 'Петров Пётр Петрович',
        'phone' => '+79000000001',
    ]),
];

try {
    $result = $client->getRecipientsReliability($recipients);

    foreach ($result as $item) {
        var_dump($item['unreliability'] ?? null);
    }
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось проверить список получателей.', 0, $exception);
}
```
