# Техническая документация для разработчика

## Назначение библиотеки

`WT Otpravkapochtaru` — библиотека Joomla 5+ для работы с двумя внешними службами Почты России:

- REST API отправки: настройки аккаунта, нормализация данных, тарифы, заказы, партии, документы, возвраты и отделения;
- SOAP-службы отслеживания: история операций, события наложенного платежа и пакетные заявки.

Пакет устанавливает библиотеку и системный плагин. Библиотека содержит код, а плагин хранит параметры подключения. Такой подход оставляет учётные данные в стандартной конфигурации Joomla и позволяет нескольким расширениям использовать один клиент.

## Подключение в Joomla

Манифест библиотеки регистрирует пространство имён `Webtolk\Otpravkapochtaru`. После установки пакета классы доступны через штатный загрузчик Joomla; вручную подключать файлы или вызывать `JLoader::registerNamespace()` не требуется.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
```

Этот код должен выполняться внутри уже загруженного приложения Joomla: компонента, плагина, модуля, консольной команды или задачи планировщика.

## Слои

| Слой | Классы | Назначение |
| --- | --- | --- |
| Прикладной фасад | `Otpravkapochtaru` | Рекомендуемые операции над аккаунтом, заказами, партиями, тарифами, отделениями и отслеживанием. |
| Настройки | `Configuration\CredentialsProvider` | Чтение параметров системного плагина или явного `Registry`/массива. |
| REST-транспорт | `Request` | Заголовки, адреса служб, JSON, двоичные файлы и транспортные ошибки. |
| SOAP-транспорт | `SoapRequest` | Создание клиентов одиночной и пакетной служб. |
| SOAP-операции | `TrackingEntity` | Формирование запросов и преобразование SOAP-объектов в массивы. |
| Сущности | `Entity\*` | Нормализация имён ключей, значения по умолчанию и проверка данных до отправки. |
| Справочники | `CountryDictionary` | Локальные данные, не расходующие лимит API. |

## Рекомендуемая точка входа

Фасад создаёт REST- и SOAP-слои с одним провайдером параметров:

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$credentials = new CredentialsProvider();
$client = new Otpravkapochtaru($credentials);
```

Если `CredentialsProvider` не передан, фасад создаст его самостоятельно. Явная передача полезна, когда одновременно нужны фасад и низкоуровневый `Request`, например для нормализации адресов.

## Настройки подключения

| Параметр | Тип | Назначение |
| --- | --- | --- |
| `access_token` | `string` | REST-токен для `Authorization: AccessToken ...`. |
| `auth_mode` | `string` | `key`/`user_key` либо режим логина и пароля. |
| `user_key` | `string` | Пользовательский ключ для `X-User-Authorization`. |
| `user_login` | `string` | Логин REST API при парной авторизации. |
| `user_password` | `string` | Пароль REST API при парной авторизации. |
| `tracking_login` | `string` | Отдельный логин SOAP-службы отслеживания. |
| `tracking_password` | `string` | Отдельный пароль SOAP-службы. |
| `http_timeout` | `int` | Время ожидания в секундах, по умолчанию 60. |

Поддерживаются старые имена `AccessToken`, `user_auth_key` и `user_key_or_login_and_password`, однако новый код должен использовать основные имена.

Секреты нельзя выводить через `var_dump`, записывать в журнал или помещать в исключение. Примеры документации проверяют лишь наличие значений.

## Общая последовательность работы

Для расчёта и создания заказа рекомендуется следующий порядок:

1. Нормализовать адрес отправителя и получателя через `POST /1.0/clean/address`.
2. Нормализовать ФИО через `POST /1.0/clean/physical`.
3. Нормализовать телефон через `POST /1.0/clean/phone`.
4. Проверить получателя методом `getRecipientReliability()`.
5. Рассчитать стоимость и срок методом `getTariffAndDeliveryPeriod()`.
6. Создать `Order` из проверенных данных.
7. Передать заказ в `createOrders()` и сохранить полученный внутренний идентификатор.
8. При необходимости изменить заказ через `editOrder()`.
9. Сформировать партию через `createBatch()`.
10. Для изменения или удаления заказа из партии сначала вызвать `returnOrdersToNew()`.

Полные исполняемые примеры находятся в главах [о тарифах](api/normalization-and-tariffs.md) и [о заказах](api/orders.md).

## Формы данных

### Обычные JSON-ответы

REST-методы возвращают ассоциативные массивы или списки:

```php
array<string, mixed>
list<array<string, mixed>>
```

Библиотека не оборачивает результат в собственный объект ответа. Имена полей сохраняются в форме внешнего API, преимущественно `kebab-case`.

### Двоичные документы

`generateDocumentPackage()`, `generateDocumentF103()` и `Request::getBinary()` возвращают:

```php
array{
    content: string,
    contentType: string,
    fileName: string|null,
    statusCode: int,
    headers: array<string, mixed>
}
```

### SOAP-ответы

SOAP-объекты рекурсивно преобразуются в обычные PHP-массивы. Одиночные и пакетные методы возвращают списки записей.

### Денежные значения

Поля вроде `total-rate`, `total-vat`, `value`, `amount` и `delivery-rate` следует считать значениями в минимальных денежных единицах внешнего контракта, обычно в копейках. Не выводите их как рубли без явного деления и правил округления.

## Наблюдаемые JSON Schema

Каталог [`api-schemas/otpravka/`](api-schemas/otpravka/README.md) содержит:

- 27 обезличенных примеров реальных ответов;
- 27 схем JSON Schema Draft 2020-12;
- индекс 29 проверенных операций;
- сведения об успешных и ошибочных ответах.

Схемы выведены из одного реального прогона. Они полезны для примеров, фиктивных ответов и проверки известной формы, но не заменяют полную спецификацию внешнего API: необязательное поле может не встретиться в наблюдаемом ответе.

## Исключения

```text
RuntimeException
└── OtpravkapochtaruException
    ├── ConfigurationException
    ├── ValidationException
    ├── TransportException
    └── TrackingException
```

| Исключение | Когда возникает |
| --- | --- |
| `ConfigurationException` | Плагин выключен, настройки пусты или отсутствуют обязательные реквизиты. |
| `ValidationException` | Сущность не содержит обязательных полей либо вложенный раздел имеет неверный тип. |
| `TransportException` | HTTP-код от 400, не-JSON ответ, неизвестный адрес службы, ошибка кодирования либо распознанный маркер ошибки API. |
| `TrackingException` | Сбой SOAP-вызова одиночной или пакетной службы. |

Рекомендуемая обработка:

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Log\Log;
use Webtolk\Otpravkapochtaru\Exception\ConfigurationException;
use Webtolk\Otpravkapochtaru\Exception\TrackingException;
use Webtolk\Otpravkapochtaru\Exception\TransportException;
use Webtolk\Otpravkapochtaru\Exception\ValidationException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();

try {
    $account = $client->getAccountInfo();
} catch (ConfigurationException $exception) {
    Log::add('Не настроено подключение к Почте России.', Log::ERROR, 'otpravkapochtaru');
} catch (ValidationException $exception) {
    Log::add($exception->getMessage(), Log::WARNING, 'otpravkapochtaru');
} catch (TransportException $exception) {
    Log::add('Ошибка REST API: ' . $exception->getMessage(), Log::ERROR, 'otpravkapochtaru');
} catch (TrackingException $exception) {
    Log::add('Ошибка SOAP-службы: ' . $exception->getMessage(), Log::ERROR, 'otpravkapochtaru');
}
```

Не добавляйте к сообщениям содержимое заголовков авторизации или параметры плагина.

## Лимит запросов

Каждое обращение к REST API расходует запрос аккаунта. `getApiLimit()` также расходует запрос. Практические следствия:

- не вызывайте `getApiLimit()` перед каждой операцией;
- применяйте пакетные методы для заказов и получателей;
- не вызывайте подряд `getAccountInfo()` и `getSettings()`, поскольку они обращаются к одному маршруту;
- не вызывайте подряд `getTariff()` и `getTariffAndDeliveryPeriod()`, поскольку они выполняют одинаковый запрос;
- сохраняйте идентификаторы заказов и партий в базе Joomla вместо повторного поиска;
- кэшируйте локально справочные ответы, если их допустимый срок жизни известен приложению.

## Безопасность операций

- Проверяйте право Joomla-пользователя до создания, изменения и удаления отправлений.
- Применяйте проверку маркера формы в контроллерах Joomla до вызова библиотеки.
- Не передавайте в путь или имя файла необработанные значения из пользовательского ввода.
- Сохраняйте двоичные документы только в контролируемой папке.
- Не публикуйте сырые ответы `getAccountInfo()` и `getShippingPoints()`: они могут содержать реквизиты и адреса аккаунта.
- Для разрушительных методов храните журнал внутренних идентификаторов, но не секретов.

## Карта документации

- [Все методы фасада](facade-method-reference.md).
- [Аккаунт и настройки](api/account-and-configuration.md).
- [Нормализация и тариф](api/normalization-and-tariffs.md).
- [Заказы и получатели](api/orders.md).
- [Партии и документы](api/batches-and-documents.md).
- [Возвраты](api/returns.md).
- [Отделения и справочники](api/post-offices-and-dictionaries.md).
- [SOAP-отслеживание](api/tracking.md).
- [Сущности данных](entities-reference.md).
- [Низкоуровневый интерфейс](low-level-api.md).
- [Реальные примеры и JSON Schema](api-schemas/otpravka/README.md).
