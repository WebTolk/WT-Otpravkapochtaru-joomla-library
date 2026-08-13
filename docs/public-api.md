# Публичный API фасада

`Webtolk\Otpravkapochtaru\Otpravkapochtaru` - основной программный интерфейс Joomla-библиотеки. Фасад читает настройки из системного плагина `wtotpravkapochtaru`, нормализует массивы Joomla/PHP в сущности `lapaygroup/russianpost 2.0.0` и возвращает массивы ответов API Почты России.

Эта версия документации написана в сравнении с прежней библиотекой 2.0: покрытие API расширено, добавлены методы аккаунта, настроек, партий, документов, возвратных отправлений, ОПС, тарифов и трекинга. Библиотеку можно использовать в своих Joomla-расширениях без прямой работы с настройками системного плагина и транспортным слоем исходной SDK-библиотеки.

Все примеры рассчитаны на выполнение внутри Joomla после установки пакета и настройки системного плагина.

## Общий шаблон

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
```

При необходимости можно передать свои настройки явно через `Registry`:

```php
<?php

declare(strict_types=1);

use Joomla\Registry\Registry;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$config = new Registry([
    'access_token' => 'YOUR_ACCESS_TOKEN',
    'user_auth_key' => 'YOUR_USER_AUTH_KEY',
    'tracking_login' => 'YOUR_TRACKING_LOGIN',
    'tracking_password' => 'YOUR_TRACKING_PASSWORD',
]);

$client = new Otpravkapochtaru($config);
```

## Аккаунт и настройки

### `getAccountInfo(): array`

Получает сведения об аккаунте отправителя, договоре, доступных ОПС и возможностях кабинета; метод не принимает входных параметров.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\Registry\Registry;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$account = new Registry($client->getAccountInfo());

echo ($account->get('api_enabled') ? 'API включен' : 'API выключен') . PHP_EOL;
echo 'ОПС: ' . count((array) $account->get('available-shipping-points', [])) . PHP_EOL;
```

Пример ответа приведен в [get-account-info.json](api-snapshots/latest/get-account-info.json); при ошибках авторизации или сетевого обмена метод передает вызывающему коду исключение SDK, не изменяя данные аккаунта или отправлений.

### `getShippingPoints(): array`

Возвращает список ОПС, доступных аккаунту отправителя; метод не принимает входных параметров.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\Utilities\ArrayHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();

foreach ($client->getShippingPoints() as $point) {
    $postcode = (string) ($point['operator-postcode'] ?? '');
    $address = (string) ($point['ops-address'] ?? '');
    $services = ArrayHelper::getColumn($point['services'] ?? [], 'code');

    echo trim($postcode . ' ' . $address) . ': ' . implode(', ', $services) . PHP_EOL;
}
```

Пример ответа приведен в [get-shipping-points.json](api-snapshots/latest/get-shipping-points.json); метод только читает доступные ОПС и не меняет состояние аккаунта.

### `getApiLimit(): array`

Возвращает лимиты API для текущего аккаунта; метод не принимает входных параметров.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\Registry\Registry;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$limit = new Registry($client->getApiLimit());

echo 'Доступно: ' . (int) $limit->get('allowed-count') . PHP_EOL;
echo 'Использовано: ' . (int) $limit->get('current-count') . PHP_EOL;
```

Пример ответа приведен в [get-api-limit.json](api-snapshots/latest/get-api-limit.json); при HTTP-ошибке, пустом теле или невалидном JSON метод выбрасывает `RuntimeException`, не меняя состояние аккаунта.

### `getSettings(): array`

Возвращает настройки аккаунта напрямую из метода `settings()` библиотеки `lapaygroup/russianpost`; в текущем коде это тот же источник данных, который использует `getAccountInfo()`, а входные параметры методу не нужны.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\Registry\Registry;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$settings = new Registry($client->getSettings());

echo 'Онлайн-баланс: ' . ($settings->get('online-balance-enabled') ? 'да' : 'нет') . PHP_EOL;
echo 'ОПС: ' . count((array) $settings->get('available-shipping-points', [])) . PHP_EOL;
```

Пример ответа приведен в [get-settings.json](api-snapshots/latest/get-settings.json); снимок обезличен, реквизиты аккаунта скрыты, а сам вызов только читает настройки.

## Заказы

### `createOrders(array $orders): array`

Создает один или несколько черновых заказов в API Почты России. Входные массивы нормализуются в сущности библиотеки `lapaygroup/russianpost`, а ключи можно передавать в `snake_case` или `kebab-case`.

Входные параметры: `$orders` - список массивов или объектов заказов.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$input = Factory::getApplication()->getInput();
$shopOrderId = StringHelper::trim($input->getString('order_id', 'demo-' . date('YmdHis')));

$client = new Otpravkapochtaru();
$result = $client->createOrders([
    [
        'order-num' => $shopOrderId,
        'postoffice-code' => '109012',
        'recipient-name' => 'Иванов Иван',
        'tel-address' => '79000000000',
        'index-to' => '455001',
        'region-to' => 'Челябинская область',
        'place-to' => 'Магнитогорск',
        'street-to' => 'Ленина',
        'house-to' => '1',
        'mail-type' => 'POSTAL_PARCEL',
        'mail-category' => 'ORDINARY',
        'mass' => 1000,
    ],
]);

print_r($result);
```

Пример ошибки безопасного прогона: [create-orders.json](api-snapshots/latest/create-orders.json). На валидных данных метод создает реальные отправления.

### `editOrder(array|object $order, int|string $id): array`

Изменяет существующий заказ по внутреннему ID Почты России.

Входные параметры: `$order` - массив или объект заказа; `$id` - ID заказа.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$input = Factory::getApplication()->getInput();
$orderId = StringHelper::trim($input->getString('russian_post_order_id'));

$client = new Otpravkapochtaru();
$result = $client->editOrder([
    'order-num' => 'shop-order-updated',
    'postoffice-code' => '109012',
    'recipient-name' => 'Иванов Иван',
    'tel-address' => '79000000000',
    'index-to' => '455001',
    'region-to' => 'Челябинская область',
    'place-to' => 'Магнитогорск',
    'street-to' => 'Ленина',
    'house-to' => '1',
    'mail-type' => 'POSTAL_PARCEL',
    'mail-category' => 'ORDINARY',
    'mass' => 1200,
], $orderId);

print_r($result);
```

Пример ошибки приведен в [edit-order.json](api-snapshots/latest/edit-order.json); при валидном идентификаторе метод изменяет существующий заказ в API Почты России.

### `findOrderById(int|string $id): array`

Ищет заказ по внутреннему идентификатору Почты России, не изменяя найденные данные и не создавая новых сущностей.

Входные параметры: `$id` - ID заказа в API Почты России.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$id = StringHelper::trim(Factory::getApplication()->getInput()->getString('russian_post_order_id'));
$client = new Otpravkapochtaru();

try {
    print_r($client->findOrderById($id));
} catch (Throwable $exception) {
    echo $exception->getMessage();
}
```

Пример ошибки `RESOURCE_NOT_FOUND` приведен в [find-order-by-id.json](api-snapshots/latest/find-order-by-id.json); метод выполняет только поиск и не меняет данные заказа.

### `findOrderByShopId(string $orderNumber): array`

Ищет отправления по номеру заказа магазина.

Входные параметры: `$orderNumber` - внешний номер заказа.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$orderNumber = StringHelper::trim(Factory::getApplication()->getInput()->getString('order_number'));
$client = new Otpravkapochtaru();
$orders = $client->findOrderByShopId($orderNumber);

foreach ($orders as $order) {
    echo (string) ($order['id'] ?? '') . PHP_EOL;
}
```

Пример ответа приведен в [find-order-by-shop-id.json](api-snapshots/latest/find-order-by-shop-id.json); метод выполняет только чтение и не меняет данные отправлений.

### `findOrderByRpo(string $rpo): array`

Ищет отправление в «Отправке» по РПО.

Входные параметры: `$rpo` - трек-номер/РПО.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$rpo = StringHelper::trim(Factory::getApplication()->getInput()->getString('rpo'));
$client = new Otpravkapochtaru();
$orders = $client->findOrderByRpo($rpo);

print_r($orders);
```

Пример ответа приведен в [find-order-by-rpo.json](api-snapshots/latest/find-order-by-rpo.json); метод выполняет только чтение и не меняет данные отправлений.

### `getRecipientReliability(array|object $recipient): array`

Проверяет одного получателя через метод API, отвечающий за оценку надежности получателей.

Входные параметры: `$recipient` - массив или объект получателя.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\Registry\Registry;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$result = new Registry($client->getRecipientReliability([
    'raw-full-name' => 'Иванов Иван',
    'raw-address' => '455001, Челябинская область, Магнитогорск, Ленина, 1',
    'raw-telephone' => '79000000000',
]));

echo (string) $result->get('unreliability') . PHP_EOL;
```

Пример ответа приведен в [get-recipient-reliability.json](api-snapshots/latest/get-recipient-reliability.json); метод только отправляет данные на проверку и не создает отправления.

### `getRecipientsReliability(array $recipients): array`

Проверяет список получателей через тот же механизм оценки надежности, который используется для проверки одного получателя.

Входные параметры: `$recipients` - список массивов или объектов получателей.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\Utilities\ArrayHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$results = $client->getRecipientsReliability([
    [
        'raw-full-name' => 'Иванов Иван',
        'raw-address' => '455001, Челябинская область, Магнитогорск, Ленина, 1',
        'raw-telephone' => '79000000000',
    ],
]);

print_r(ArrayHelper::getColumn($results, 'unreliability'));
```

Пример ответа приведен в [get-recipients-reliability.json](api-snapshots/latest/get-recipients-reliability.json); метод только отправляет список получателей на проверку и не создает отправления.

### `deleteOrders(array $orderIds): array`

Удаляет черновые заказы в API Почты России, поэтому вызывающий код должен передавать только подтвержденный список внутренних идентификаторов.

Входные параметры: `$orderIds` - список внутренних ID заказов.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\Utilities\ArrayHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$orderIds = ArrayHelper::toInteger([123456789]);
$client = new Otpravkapochtaru();
$result = $client->deleteOrders($orderIds);

print_r($result['errors'] ?? $result);
```

Пример частичной ошибки `NOT_FOUND` приведен в [delete-orders.json](api-snapshots/latest/delete-orders.json); при валидных ID метод удаляет реальные черновые заказы.

### `returnOrdersToNew(array $orderIds): array`

Возвращает заказы в состояние `new`.

Входные параметры: `$orderIds` - список внутренних ID заказов.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\Utilities\ArrayHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$orderIds = ArrayHelper::toInteger([123456789]);
$client = new Otpravkapochtaru();
$result = $client->returnOrdersToNew($orderIds);

print_r($result['errors'] ?? $result);
```

Пример частичной ошибки `NOT_FOUND` приведен в [return-orders-to-new.json](api-snapshots/latest/return-orders-to-new.json); при валидных ID метод меняет состояние заказов.

## Партии и документы

### `createBatch(array $orderIds, ?string $sendingDate = null, bool $useOnlineBalance = false): array`

Создает партию из существующих отправлений.

Входные параметры: `$orderIds` - ID заказов; `$sendingDate` - дата сдачи `YYYY-MM-DD`; `$useOnlineBalance` - оплата с онлайн-баланса.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\Utilities\ArrayHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$orderIds = ArrayHelper::toInteger([123456789]);
$result = $client->createBatch($orderIds, date('Y-m-d'), false);

print_r($result);
```

Пример частичной ошибки приведен в [create-batch.json](api-snapshots/latest/create-batch.json); при реальных ID метод создает партию и переводит выбранные отправления в соответствующее состояние API.

### `getAllBatches(?string $mailType = null, ?string $mailCategory = null, ?int $size = null, string $sort = 'ask', ?int $page = null): array`

Возвращает список партий с фильтрацией и пагинацией.

Входные параметры: `$mailType`, `$mailCategory`, `$size`, `$sort`, `$page`.

Пример:

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$batches = $client->getAllBatches(size: 20, sort: 'ask', page: 0);

print_r($batches);
```

Пример ответа приведен в [get-all-batches.json](api-snapshots/latest/get-all-batches.json); метод только читает список партий.

### `getOrdersInBatch(string $batchName, ?int $size = null, string $sort = 'ask', ?int $page = null): array`

Возвращает отправления внутри партии.

Входные параметры: `$batchName`, `$size`, `$sort`, `$page`.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$batchName = StringHelper::trim(Factory::getApplication()->getInput()->getString('batch'));
$client = new Otpravkapochtaru();

print_r($client->getOrdersInBatch($batchName, 50, 'ask', 0));
```

Пример ошибки для несуществующей партии приведен в [get-orders-in-batch.json](api-snapshots/latest/get-orders-in-batch.json); метод только читает состав партии.

### `generateDocumentPackage(string $batchName, string $printType = 'paper', string $printTypeForm = 'one-sided'): array`

Генерирует комплект документов партии.

Входные параметры: `$batchName`; `$printType` - `paper` или `thermo`; `$printTypeForm` - `one-sided` или `two-sided`.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$batchName = StringHelper::trim(Factory::getApplication()->getInput()->getString('batch'));
$client = new Otpravkapochtaru();
$document = $client->generateDocumentPackage($batchName, 'paper', 'one-sided');

File::write(JPATH_ROOT . '/tmp/russian-post-documents.zip', $document['content'] ?? '');
```

Пример ошибки приведен в [generate-document-package.json](api-snapshots/latest/generate-document-package.json); метод запрашивает формирование документа на стороне API, а локальная запись файла в `tmp` выполняется только кодом примера.

### `generateDocumentF103(string $batchName): array`

Генерирует форму Ф103 для партии.

Входные параметры: `$batchName`.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$batchName = StringHelper::trim(Factory::getApplication()->getInput()->getString('batch'));
$client = new Otpravkapochtaru();
$document = $client->generateDocumentF103($batchName);

File::write(JPATH_ROOT . '/tmp/f103.pdf', $document['content'] ?? '');
```

Пример ошибки приведен в [generate-document-f103.json](api-snapshots/latest/generate-document-f103.json); метод запрашивает формирование документа на стороне API, а локальная запись файла в `tmp` выполняется только кодом примера.

## Возвратные отправления

### `createReturnShipment(string $directBarcode, string $mailType = 'UNDEFINED'): array`

Создает возвратное отправление по штрихкоду прямого отправления.

Входные параметры: `$directBarcode` - штрихкод; `$mailType` - тип отправления.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$barcode = StringHelper::trim(Factory::getApplication()->getInput()->getString('barcode'));
$client = new Otpravkapochtaru();
$result = $client->createReturnShipment($barcode, 'UNDEFINED');

print_r($result);
```

Пример ошибки `DIRECT_SHIPMENT_NOT_FOUND` приведен в [create-return-shipment.json](api-snapshots/latest/create-return-shipment.json); при валидном штрихкоде метод создает возвратное отправление.

### `createReturnShipments(array $returnShipments): array`

Массово создает возвратные отправления.

Входные параметры: `$returnShipments` - список массивов или объектов возвратных отправлений.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\Registry\Registry;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$returnShipment = new Registry([
    'postoffice-code' => '410012',
    'address-from' => ['index' => '410012', 'place' => 'Саратов', 'street' => 'Московская', 'house' => '109'],
    'address-to' => ['index' => '455001', 'place' => 'Магнитогорск', 'street' => 'Ленина', 'house' => '1'],
]);

$client = new Otpravkapochtaru();
$result = $client->createReturnShipments([$returnShipment->toArray()]);

print_r($result);
```

Пример ошибки входных данных приведен в [create-return-shipments.json](api-snapshots/latest/create-return-shipments.json); при валидном массиве метод создает возвратные отправления.

### `editReturnShipment(array|object $returnShipment, string $rpo): array`

Изменяет возвратное отправление по РПО.

Входные параметры: `$returnShipment` - новые данные; `$rpo` - штрихкод возвратного отправления.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$rpo = StringHelper::trim(Factory::getApplication()->getInput()->getString('rpo'));
$returnShipmentData = new Registry([
    'postoffice-code' => '410012',
    'mail-type' => 'POSTAL_PARCEL',
    'address-from' => ['index' => '410012', 'place' => 'Саратов', 'street' => 'Московская', 'house' => '109'],
    'address-to' => ['index' => '455001', 'place' => 'Магнитогорск', 'street' => 'Ленина', 'house' => '1'],
]);

$client = new Otpravkapochtaru();
$result = $client->editReturnShipment($returnShipmentData->toArray(), $rpo);

print_r($result);
```

Пример ошибки приведен в [edit-return-shipment.json](api-snapshots/latest/edit-return-shipment.json); при валидном РПО метод изменяет возвратное отправление.

### `deleteReturnShipment(string $rpo): array`

Удаляет возвратное отправление.

Входные параметры: `$rpo` - штрихкод возвратного отправления.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$rpo = StringHelper::trim(Factory::getApplication()->getInput()->getString('rpo'));
$client = new Otpravkapochtaru();
$result = $client->deleteReturnShipment($rpo);

print_r($result);
```

Пример ошибки `RETURN_SHIPMENT_NOT_FOUND` приведен в [delete-return-shipment.json](api-snapshots/latest/delete-return-shipment.json); при валидном РПО метод удаляет возвратное отправление.

## Тарифы

### `getTariff(int|string $objectId, array $params, array $services = []): array`

Рассчитывает тариф для объекта тарификации API Почты России.

Входные параметры: `$objectId` - ID объекта тарификации; `$params` - параметры отправления; `$services` - дополнительные услуги.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\Registry\Registry;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$params = new Registry([
    'from-index' => '410012',
    'to-index' => '455001',
    'mail-type' => 'POSTAL_PARCEL',
    'mail-category' => 'ORDINARY',
    'mass' => 1000,
]);

$client = new Otpravkapochtaru();
$tariff = $client->getTariff(27030, $params->toArray());

print_r($tariff);
```

Пример ошибки безопасного прогона приведен в [get-tariff.json](api-snapshots/latest/get-tariff.json); метод выполняет расчетный запрос и не меняет отправления.

### `getTariffAndDeliveryPeriod(int|string $objectId, array $params, array $services = []): array`

Рассчитывает тариф и срок доставки.

Входные параметры: `$objectId`, `$params`, `$services`.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\Registry\Registry;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$params = new Registry([
    'from-index' => '410012',
    'to-index' => '455001',
    'mail-type' => 'POSTAL_PARCEL',
    'mail-category' => 'ORDINARY',
    'mass' => 1000,
]);

$client = new Otpravkapochtaru();
$delivery = $client->getTariffAndDeliveryPeriod(27030, $params->toArray());

print_r($delivery);
```

Пример ошибки безопасного прогона приведен в [get-tariff-and-delivery-period.json](api-snapshots/latest/get-tariff-and-delivery-period.json); метод выполняет расчетный запрос и не меняет отправления.

## Справочники и ОПС

### `getCountryList(): array`

Возвращает список стран из справочника Почты России; метод не принимает входных параметров.

Пример:

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$countries = $client->getCountryList();

print_r(array_slice($countries, 0, 10));
```

Пример ответа приведен в [get-country-list.json](api-snapshots/latest/get-country-list.json); метод только читает справочник стран.

### `searchPostOfficeByIndex(int|string $postalCode, ?string $latitude = null, ?string $longitude = null, ?string $currentDateTime = null, bool $filterByOfficeType = true, bool $ufpsPostalCode = false): array`

Ищет ОПС по индексу. Дополнительные координаты и дата позволяют уточнить доступность услуг.

Входные параметры: `$postalCode`, `$latitude`, `$longitude`, `$currentDateTime`, `$filterByOfficeType`, `$ufpsPostalCode`.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$postalCode = StringHelper::trim(Factory::getApplication()->getInput()->getString('postcode', '410012'));
$client = new Otpravkapochtaru();
$office = $client->searchPostOfficeByIndex($postalCode);

print_r($office);
```

Пример ответа приведен в [search-post-office-by-index.json](api-snapshots/latest/search-post-office-by-index.json); метод только читает справочник ОПС.

### `searchPostOfficeByAddress(string $address, int $count = 3): array`

Ищет ближайшие или наиболее подходящие ОПС по текстовому адресу, который передает вызывающее расширение.

Входные параметры: `$address`; `$count` - максимальное количество вариантов.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$address = StringHelper::trim(Factory::getApplication()->getInput()->getString('address', 'Саратов, Московская, 109'));
$client = new Otpravkapochtaru();
$offices = $client->searchPostOfficeByAddress($address, 3);

print_r($offices);
```

Пример ответа приведен в [search-post-office-by-address.json](api-snapshots/latest/search-post-office-by-address.json); метод только читает справочник ОПС.

### `searchPostOfficeByCoordinates(array $params): array`

Ищет ОПС рядом с указанными координатами и возвращает варианты из справочника Почты России.

Входные параметры: `$params` - массив с координатами и ограничениями выборки.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\Registry\Registry;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$params = new Registry([
    'latitude' => '51.533557',
    'longitude' => '46.034257',
    'top' => 3,
]);

$client = new Otpravkapochtaru();
$offices = $client->searchPostOfficeByCoordinates($params->toArray());

print_r($offices);
```

Пример ответа приведен в [search-post-office-by-coordinates.json](api-snapshots/latest/search-post-office-by-coordinates.json); метод только читает справочник ОПС.

### `getPostOfficeServices(int|string $postalCode, ?string $serviceGroup = null): array`

Возвращает услуги ОПС по индексу.

Входные параметры: `$postalCode`; `$serviceGroup` - необязательная группа услуг.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$postalCode = StringHelper::trim(Factory::getApplication()->getInput()->getString('postcode', '410012'));
$client = new Otpravkapochtaru();
$services = $client->getPostOfficeServices($postalCode);

print_r($services);
```

Пример ответа приведен в [get-post-office-services.json](api-snapshots/latest/get-post-office-services.json); метод только читает справочник услуг ОПС.

### `getPostalCodesInLocality(string $locality, string $region = '', string $district = ''): array`

Возвращает индексы в населенном пункте.

Входные параметры: `$locality`, `$region`, `$district`.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$locality = StringHelper::trim(Factory::getApplication()->getInput()->getString('locality', 'Саратов'));
$client = new Otpravkapochtaru();
$codes = $client->getPostalCodesInLocality($locality, 'Саратовская область');

print_r($codes);
```

Пример ответа приведен в [get-postal-codes-in-locality.json](api-snapshots/latest/get-postal-codes-in-locality.json); метод только читает справочник индексов.

## Трекинг

### `getOperationsByRpo(string $rpo, string $lang = self::TRACKING_DEFAULT_LANG): array`

Возвращает историю операций по РПО через SOAP-трекинг.

Входные параметры: `$rpo`; `$lang` - язык ответа, по умолчанию значение фасада.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$rpo = StringHelper::trim(Factory::getApplication()->getInput()->getString('rpo'));
$client = new Otpravkapochtaru();
$history = $client->getOperationsByRpo($rpo);

print_r($history);
```

Пример ответа приведен в [get-operations-by-rpo.json](api-snapshots/latest/get-operations-by-rpo.json); для вызова нужны `ext-soap` и настройки трекинга, а данные отправлений при этом не изменяются.

### `getNpayInfo(string $rpo, string $lang = self::TRACKING_DEFAULT_LANG): array`

Возвращает информацию о наложенном платеже по РПО.

Входные параметры: `$rpo`; `$lang`.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$rpo = StringHelper::trim(Factory::getApplication()->getInput()->getString('rpo'));
$client = new Otpravkapochtaru();
$npay = $client->getNpayInfo($rpo);

print_r($npay);
```

Пример ответа приведен в [get-npay-info.json](api-snapshots/latest/get-npay-info.json); метод обращается к SOAP-трекингу и не меняет данные отправлений.

### `getTickets(array $rpoList, string $lang = self::TRACKING_DEFAULT_LANG): array`

Создает или получает тикеты пакетного трекинга по списку РПО.

Входные параметры: `$rpoList`; `$lang`.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$input = Factory::getApplication()->getInput();
$rpo = StringHelper::trim($input->getString('rpo'));

$client = new Otpravkapochtaru();
$tickets = $client->getTickets([$rpo]);

print_r($tickets);
```

Пример ответа приведен в [get-tickets.json](api-snapshots/latest/get-tickets.json); метод не меняет отправления, но сервис Почты России может создать тикет пакетного трекинга.

### `getOperationsByTicket(string $ticket): array`

Возвращает операции пакетного трекинга по тикету.

Входные параметры: `$ticket` - идентификатор тикета, полученный из `getTickets()`.

Пример:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$ticket = StringHelper::trim(Factory::getApplication()->getInput()->getString('ticket'));
$client = new Otpravkapochtaru();

try {
    print_r($client->getOperationsByTicket($ticket));
} catch (Throwable $exception) {
    echo $exception->getMessage();
}
```

Пример ошибки для несуществующего тикета приведен в [get-operations-by-ticket.json](api-snapshots/latest/get-operations-by-ticket.json); метод только читает результат пакетного трекинга.
