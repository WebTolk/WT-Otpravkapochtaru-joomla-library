# Партии и печатные документы

Партия объединяет подготовленные отправления для сдачи в отделение. После включения заказа в партию его нельзя считать обычным редактируемым черновиком: перед изменением или удалением заказ возвращают методом `returnOrdersToNew()`.

<a id="createbatch"></a>
## `createBatch(array $orderIds, ?string $sendingDate = null, bool $useOnlineBalance = false): array`

**Что делает.** Создаёт партию из переданных внутренних идентификаторов заказов.

**Зачем нужен.** Партия необходима для подготовки сопроводительных документов и организованной сдачи нескольких отправлений.

**Как работает.** Выполняет `POST /1.0/user/shipment`. Идентификаторы передаются в теле. Параметры `sending-date` и `use-online-balance` добавляются в строку запроса только тогда, когда дата не пуста.

| Параметр | Тип | Обязателен | Назначение |
| --- | --- | --- | --- |
| `$orderIds` | `list<int\|string>` | да | Идентификаторы заказов из `result-ids`. |
| `$sendingDate` | `string\|null` | нет | Плановая дата отправки в формате, который принимает API. |
| `$useOnlineBalance` | `bool` | нет | Признак оплаты с баланса; учитывается только вместе с датой. |

Наблюдаемый ответ содержит массив `batches` и список `result-ids`: [пример](../api-schemas/otpravka/examples/create-batch.response.json), [JSON Schema](../api-schemas/otpravka/schemas/create-batch.response.schema.json).

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$orderId = Factory::getApplication()->getInput()->getInt('russian_post_order_id');

if ($orderId <= 0) {
    throw new InvalidArgumentException('Не передан идентификатор заказа.');
}

try {
    $result = $client->createBatch(
        orderIds: [$orderId],
        sendingDate: date('Y-m-d'),
        useOnlineBalance: false,
    );

    $batch = $result['batches'][0] ?? [];
    $batchName = $batch['batch-name'] ?? null;

    var_dump([
        'batch_name' => $batchName,
        'batch_status' => $batch['batch-status'] ?? null,
        'accepted_order_ids' => $result['result-ids'] ?? [],
    ]);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось создать партию.', 0, $exception);
}
```

<a id="getallbatches"></a>
## `getAllBatches(?string $mailType = null, ?string $mailCategory = null, ?int $size = null, string $sort = 'ask', ?int $page = null): array`

**Что делает.** Получает список партий с необязательной фильтрацией и разбиением на страницы.

**Зачем нужен.** Метод применяется в журнале партий, при поиске партии для печати и для сверки состояния отправлений.

**Как работает.** Выполняет `GET /1.0/batch`. Непустые фильтры преобразуются в параметры `mailType`, `mailCategory`, `size`, `sort` и `page`.

| Параметр | Тип | По умолчанию | Назначение |
| --- | --- | --- | --- |
| `$mailType` | `string\|null` | `null` | Тип отправления. |
| `$mailCategory` | `string\|null` | `null` | Категория отправления. |
| `$size` | `int\|null` | `null` | Размер страницы. |
| `$sort` | `string` | `ask` | Значение дословно передаётся внешнему API. |
| `$page` | `int\|null` | `null` | Номер страницы, обычно начиная с нуля. |

В реальном ответе был получен список партий: [пример](../api-schemas/otpravka/examples/get-all-batches.response.json), [JSON Schema](../api-schemas/otpravka/schemas/get-all-batches.response.schema.json).

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();

try {
    $batches = $client->getAllBatches(
        mailType: 'POSTAL_PARCEL',
        mailCategory: 'ORDINARY',
        size: 20,
        sort: 'ask',
        page: 0,
    );

    foreach ($batches as $batch) {
        var_dump([
            'name' => $batch['batch-name'] ?? null,
            'status' => $batch['batch-status'] ?? null,
            'shipments' => $batch['shipment-count'] ?? null,
        ]);
    }
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось получить список партий.', 0, $exception);
}
```

<a id="getordersinbatch"></a>
## `getOrdersInBatch(string $batchName, ?int $size = null, string $sort = 'ask', ?int $page = null): array`

**Что делает.** Получает отправления, входящие в указанную партию.

**Зачем нужен.** Метод позволяет сверить состав партии, показать его менеджеру и получить присвоенные штрихкоды.

**Как работает.** Выполняет `GET /1.0/batch/{batchName}/shipment` с параметрами разбиения на страницы.

Наблюдаемые данные: [пример](../api-schemas/otpravka/examples/get-orders-in-batch.response.json) и [JSON Schema](../api-schemas/otpravka/schemas/get-orders-in-batch.response.schema.json).

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$batchName = Factory::getApplication()->getInput()->getString('batch_name');

try {
    $orders = $client->getOrdersInBatch(
        batchName: $batchName,
        size: 50,
        sort: 'ask',
        page: 0,
    );

    foreach ($orders as $order) {
        var_dump([
            'id' => $order['id'] ?? null,
            'order_number' => $order['order-num'] ?? null,
            'barcode' => $order['barcode'] ?? null,
        ]);
    }
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось получить состав партии.', 0, $exception);
}
```

<a id="generatedocumentpackage"></a>
## `generateDocumentPackage(string $batchName, string $printType = 'paper', string $printTypeForm = 'one-sided'): array`

**Что делает.** Загружает ZIP-архив печатных документов партии.

**Зачем нужен.** Архив используется для печати этикеток и сопроводительных форм перед сдачей отправлений.

**Как работает.** Выполняет двоичный `GET /1.0/forms/{batchName}/zip-all`. Имя файла извлекается из `Content-Disposition` и очищается от путей и недопустимых символов.

| Ключ результата | Тип | Назначение |
| --- | --- | --- |
| `content` | `string` | Двоичное содержимое файла. |
| `contentType` | `string` | Тип содержимого, обычно `application/zip`. |
| `fileName` | `string\|null` | Безопасное имя из заголовка ответа. |
| `statusCode` | `int` | Код HTTP. |
| `headers` | `array<string, mixed>` | Исходные заголовки ответа. |

В прогоне 2026-07-11 метод получил HTTP 400 для новой партии в состоянии `CREATED`; успешное двоичное тело не было получено, поэтому JSON Schema для него не создавалась.

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$batchName = Factory::getApplication()->getInput()->getString('batch_name');
$targetDirectory = JPATH_ROOT . '/tmp/russian-post-documents';

if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true) && !is_dir($targetDirectory)) {
    throw new RuntimeException('Не удалось создать папку документов.');
}

try {
    $document = $client->generateDocumentPackage($batchName);
    $fileName = $document['fileName'] ?: 'russian-post-documents.zip';
    $target = $targetDirectory . '/' . $fileName;

    if (file_put_contents($target, $document['content']) === false) {
        throw new RuntimeException('Не удалось записать архив документов.');
    }

    var_dump(['file' => $target, 'content_type' => $document['contentType']]);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Почта России не сформировала комплект документов.', 0, $exception);
}
```

<a id="generatedocumentf103"></a>
## `generateDocumentF103(string $batchName): array`

**Что делает.** Загружает форму Ф103 для партии.

**Зачем нужен.** Форма содержит список отправлений и применяется при передаче партии оператору почтовой связи.

**Как работает.** Выполняет двоичный `GET /1.0/forms/{batchName}/f103pdf` и возвращает тот же тип массива, что `generateDocumentPackage()`.

В реальном прогоне маршрут вернул HTTP 400, а у партии было `electronic-f103=false`. Схема успешного двоичного ответа описывается таблицей выше.

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$batchName = Factory::getApplication()->getInput()->getString('batch_name');
$targetDirectory = JPATH_ROOT . '/tmp/russian-post-documents';

if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true) && !is_dir($targetDirectory)) {
    throw new RuntimeException('Не удалось создать папку документов.');
}

try {
    $document = $client->generateDocumentF103($batchName);
    $target = $targetDirectory . '/f103-' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $batchName) . '.pdf';

    if (file_put_contents($target, $document['content']) === false) {
        throw new RuntimeException('Не удалось записать форму Ф103.');
    }

    var_dump(['file' => $target, 'status' => $document['statusCode']]);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Почта России не сформировала Ф103.', 0, $exception);
}
```
