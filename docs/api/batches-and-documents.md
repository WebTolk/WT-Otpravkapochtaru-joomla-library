# Партии и документы

Партии и печатные формы вызываются через `otpravkaApi()`. Фасад не нормализует даты, типы печати и двоичные ответы: возвращается то, что возвращает `lapaygroup/russianpost`, включая `Psr\Http\Message\UploadedFileInterface` для файлов.

## Партии

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$api = $client->otpravkaApi();

$batch = $api->createBatch([123456], new \DateTimeImmutable('tomorrow'), false);
$batches = $api->getAllBatches(size: 20, sort: 'ask', page: 0);
$orders = $api->getOrdersInBatch('BATCH-NAME', 50, 'ask', 0);
```

Идентификатор заказа и имя партии должны существовать в вашем аккаунте. `createBatch()` переносит заказы в партию и потому меняет состояние аккаунта.

## Документы

```php
<?php

declare(strict_types=1);

use LapayGroup\RussianPost\Providers\OtpravkaApi;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();

$zip = $client->otpravkaApi()->generateDocPackage(
    'BATCH-NAME',
    OtpravkaApi::PRINT_FILE,
    OtpravkaApi::PRINT_TYPE_PAPER,
    OtpravkaApi::PRINT_ONE_SIDED
);

$f103 = $client->otpravkaApi()->generateDocF103('BATCH-NAME', OtpravkaApi::PRINT_FILE);
```

Если нужно сохранить файл в Joomla, работайте с полученным `UploadedFileInterface`: прочитайте поток, имя клиента и тип содержимого стандартными PSR-методами.
