# WT Otpravkapochtaru для Joomla

`WT Otpravkapochtaru` — пакет для Joomla 5+, который помогает расширениям сайта работать с API «Отправка» Почты России и SOAP-сервисом отслеживания. В пакет входят библиотека и системный плагин с настройками подключения.

Документация ниже подготовлена для публикации в карточке проекта SW JProjects. Она не заменяет полную справку разработчика в репозитории, а объясняет рабочие сценарии: как подключиться, подготовить данные, рассчитать доставку, создать отправление, собрать партию, получить документы, оформить возврат и запросить сведения по трекингу.

Официальная спецификация Почты России доступна по адресу: <https://otpravka.pochta.ru/specification#/main>.

## Что покрывает библиотека

Официальная спецификация API организована вокруг крупных разделов: авторизация, данные, заказы, партии, документы, возвраты, настройки, поиск отделений, справочники свойств и отдельные сервисные блоки. Публичный фасад библиотеки повторяет эту модель там, где это нужно обычной интеграции интернет-магазина или компонента Joomla.

Поддерживаются:

- получение настроек аккаунта, точек сдачи и текущего остатка запросов к API;
- нормализация адресов, ФИО и телефонов перед созданием отправления;
- расчёт тарифа и срока доставки через актуальный REST-метод Почты России;
- создание, поиск, изменение, удаление и возврат заказов в состояние «Новые»;
- проверка надёжности получателя;
- создание партий, просмотр партий и заказов внутри партии;
- генерация пакета печатных документов и формы Ф103;
- создание, изменение и удаление возвратных отправлений;
- поиск отделений по индексу, адресу и координатам, получение сервисов отделения;
- локальный список стран;
- SOAP-отслеживание операций по РПО и работа с билетами пакетного трекинга.

Не реализованы как отдельные публичные методы:

- архив партий и долгосрочное хранение;
- временные интервалы и бронирование таймслотов;
- заявления на дополнительные услуги;
- пользовательские сессии API;
- все дополнительные печатные формы из официальной спецификации, кроме пакета документов и Ф103.

Такое ограничение сделано осознанно: библиотека закрывает основной поток отправки и отслеживания, не превращая Joomla-пакет в полную копию официальной спецификации.

## Установка и настройка

Установите ZIP-пакет через стандартный менеджер расширений Joomla. После установки откройте плагин `System - WT Otpravkapochtaru` и заполните параметры подключения:

- токен доступа приложения;
- режим авторизации;
- пользовательский ключ или пару «логин и пароль»;
- отдельные учётные данные SOAP-трекинга, если нужен трекинг;
- таймаут HTTP-запросов.

Для промышленного сайта храните реальные ключи только в настройках Joomla или в своём защищённом поставщике конфигурации. Не вставляйте их в код, примеры, журналы и публичную документацию.

## Базовый поток отправки

В рабочем сценарии не стоит сразу создавать заказ из пользовательской формы. Сначала приведите входные данные к виду, который ожидает Почта России.

1. Нормализуйте адрес отправителя и получателя.
2. Нормализуйте ФИО и телефон получателя.
3. Используйте нормализованный индекс и адрес в расчёте тарифа.
4. Соберите заказ из нормализованных данных.
5. Создайте заказ в «Новых».
6. После проверки нескольких заказов сформируйте партию.
7. Получите печатные документы.

Такой порядок снижает количество ошибок API и делает поведение интеграции предсказуемым.

## Пример: нормализация и расчёт доставки

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$credentials = new CredentialsProvider([
    'access_token' => 'YOUR_ACCESS_TOKEN',
    'auth_mode' => 'login_password',
    'user_login' => 'YOUR_USER_LOGIN',
    'user_password' => 'YOUR_USER_PASSWORD',
]);

$client = new Otpravkapochtaru($credentials);

$fromAddress = $client->cleanAddress('410000, Саратов, ул. Московская, 1');
$toAddress = $client->cleanAddress('685000, Магадан, проспект Ленина, 1');
$recipient = $client->cleanPhysical('Иванов Иван Иванович');
$phone = $client->cleanPhone('+7 900 000-00-00');

$tariff = $client->getTariffAndDeliveryPeriod(
    '23030',
    [
        'index-from' => $fromAddress[0]['index'] ?? '410000',
        'index-to' => $toAddress[0]['index'] ?? '685000',
        'mail-category' => 'ORDINARY',
        'mail-type' => 'POSTAL_PARCEL',
        'mass' => 500,
        'fragile' => false,
        'sms-notice-recipient' => 0,
        'with-order-of-notice' => false,
        'with-simple-notice' => false,
    ]
);

echo 'Получатель: ' . ($recipient[0]['original-fio'] ?? 'не определён') . PHP_EOL;
echo 'Телефон: ' . ($phone[0]['phone'] ?? 'не определён') . PHP_EOL;
echo 'Стоимость: ' . (($tariff['total-rate'] ?? 0) / 100) . ' руб.' . PHP_EOL;
echo 'Срок: ' . ($tariff['delivery-time'] ?? 'не рассчитан') . PHP_EOL;
```

В ответах API денежные значения обычно приходят в копейках. Поэтому перед показом пользователю сумму нужно делить на `100` и форматировать в соответствии с интерфейсом сайта.

## Пример: создание заказа

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Entity\Order;
use Webtolk\Otpravkapochtaru\Entity\Recipient;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru(new CredentialsProvider([
    'access_token' => 'YOUR_ACCESS_TOKEN',
    'auth_mode' => 'login_password',
    'user_login' => 'YOUR_USER_LOGIN',
    'user_password' => 'YOUR_USER_PASSWORD',
]));

$normalizedAddress = $client->cleanAddress('685000, Магадан, проспект Ленина, 1');
$normalizedPerson = $client->cleanPhysical('Иванов Иван Иванович');
$normalizedPhone = $client->cleanPhone('+7 900 000-00-00');

$recipient = Recipient::create([
    'address-type-to' => 'DEFAULT',
    'index-to' => $normalizedAddress[0]['index'] ?? '685000',
    'region-to' => $normalizedAddress[0]['region'] ?? 'Магаданская область',
    'place-to' => $normalizedAddress[0]['place'] ?? 'Магадан',
    'street-to' => $normalizedAddress[0]['street'] ?? 'проспект Ленина',
    'house-to' => $normalizedAddress[0]['house'] ?? '1',
    'recipient-name' => $normalizedPerson[0]['original-fio'] ?? 'Иванов Иван Иванович',
    'tel-address' => $normalizedPhone[0]['phone'] ?? '79000000000',
]);

$order = Order::create([
    'order-num' => 'JSHOP-10001',
    'mail-type' => 'POSTAL_PARCEL',
    'mail-category' => 'ORDINARY',
    'mass' => 500,
    'transport-type' => 'SURFACE',
    'payment-method' => 'CASHLESS',
    'recipient' => $recipient->toArray(),
]);

$created = $client->createOrders([$order]);

foreach ($created as $item) {
    echo 'Создан заказ API: ' . ($item['result-ids'][0] ?? 'без идентификатора') . PHP_EOL;
}
```

Для изменения уже созданного заказа используйте `editOrder()`, для удаления заказов из «Новых» — `deleteOrders()`. Если заказ нужно вернуть в «Новые», используйте `returnOrdersToNew()`.

## Партии и документы

Когда заказы проверены и готовы к передаче в отделение, сформируйте партию:

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru(new CredentialsProvider([
    'access_token' => 'YOUR_ACCESS_TOKEN',
    'auth_mode' => 'login_password',
    'user_login' => 'YOUR_USER_LOGIN',
    'user_password' => 'YOUR_USER_PASSWORD',
]));

$batch = $client->createBatch([123456789, 123456790]);
$batchName = $batch['batch-name'] ?? null;

if ($batchName !== null) {
    $zip = $client->generateDocumentPackage($batchName);
    file_put_contents(JPATH_ROOT . '/tmp/' . $batchName . '.zip', $zip['body']);
}
```

Методы документов возвращают двоичные данные. Сохраняйте их в защищённое место и не выводите в браузер без корректных заголовков `Content-Type` и `Content-Disposition`.

## Возвраты

Возвратное отправление можно создать по ШПИ прямого отправления или как отдельное возвратное отправление. Для отдельного возврата используйте сущность `ReturnShipment`, если хотите явно собрать payload и переиспользовать его в тестах.

Основные методы:

- `createReturnShipment()` создаёт возврат для ранее созданного отправления;
- `createReturnShipments()` создаёт отдельные возвратные отправления;
- `editReturnShipment()` меняет отдельное возвратное отправление;
- `deleteReturnShipment()` удаляет отдельное возвратное отправление.

## Отделения и справочники

Методы поиска отделений полезны до создания заказа: с их помощью можно проверить индекс, найти ближайшее отделение и узнать доступные сервисы.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru(new CredentialsProvider([
    'access_token' => 'YOUR_ACCESS_TOKEN',
    'auth_mode' => 'login_password',
    'user_login' => 'YOUR_USER_LOGIN',
    'user_password' => 'YOUR_USER_PASSWORD',
]));

$office = $client->searchPostOfficeByIndex('685000');
$services = $client->getPostOfficeServices('685000');

var_dump($office, $services);
```

Список стран хранится локально и не расходует лимит REST API.

## SOAP-отслеживание

REST API «Отправка» отвечает за оформление отправлений. Операции трекинга выполняются через SOAP-сервис Почты России, поэтому для них нужны отдельные учётные данные.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru(new CredentialsProvider([
    'tracking_login' => 'YOUR_TRACKING_LOGIN',
    'tracking_password' => 'YOUR_TRACKING_PASSWORD',
]));

$operations = $client->getOperationsByRpo('12345678901234');

foreach ($operations as $operation) {
    echo ($operation['operationName'] ?? 'Операция') . PHP_EOL;
}
```

Если у аккаунта нет доступа к SOAP-трекингу, REST-сценарии отправки всё равно могут работать.

## Реальные схемы ответов

В репозитории есть приложение с наблюдаемыми JSON Schema, полученными на реальных запросах отправки. Схемы находятся в `docs/api-schemas/otpravka/` и не попадают в ZIP-пакет Joomla.

Используйте эти схемы как инженерную опору для документации и тестов, но не считайте их полной официальной спецификацией. API может вернуть дополнительные поля, если Почта России изменит контракт или включит новые возможности аккаунта.

## Где читать дальше

- `README.md` — быстрый старт и установка.
- `docs/developer-api.md` — полная техническая справка.
- `docs/facade-method-reference.md` — справочник публичных методов фасада.
- `docs/api/*.md` — подробные сценарии по разделам.
- `docs/entities-reference.md` — сущности `Order`, `Recipient`, `ReturnShipment` и другие payload-объекты.
- `docs/low-level-api.md` — низкоуровневый транспорт, SOAP и провайдер учётных данных.

## Рекомендации перед публикацией

Для страницы SW JProjects лучше использовать HTML-фрагмент, собранный из этой документации. Он уже разделён на смысловые блоки, почти не использует таблицы и подходит для карточки проекта: сначала назначение, затем рабочий поток, затем примеры и ограничения.

Перед публикацией проверьте:

- что проект остаётся неопубликованным, если расширение ещё нельзя скачивать с сайта;
- что в тексте нет реальных ключей, телефонов клиентов, ШПИ и адресов заказов;
- что ссылки на полную документацию ведут в публичный репозиторий или на подготовленные страницы сайта;
- что скриншоты настроек загружены в языковые галереи проекта.
