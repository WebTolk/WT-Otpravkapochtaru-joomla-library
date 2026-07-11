# Справочник публичного фасада

Класс `Webtolk\Otpravkapochtaru\Otpravkapochtaru` является рекомендуемой точкой входа. Он объединяет REST API отправки, сведения об отделениях, локальный справочник стран и SOAP-отслеживание.

Каждая тематическая глава содержит для каждого метода:

- назначение и прикладную причину использования;
- фактический маршрут и внутренние преобразования;
- параметры и PHP-типы;
- форму результата;
- ссылку на обезличенный реальный ответ и JSON Schema, если вызов был проверен;
- самостоятельный пример с подключением пространств имён и созданием клиента.

## Создание клиента

| Сигнатура | Глава |
| --- | --- |
| `__construct(?CredentialsProvider $credentialsProvider = null)` | [Аккаунт и настройки подключения](api/account-and-configuration.md#construct) |

## Аккаунт и настройки

| Метод | Краткое назначение | Глава |
| --- | --- | --- |
| `getAccountInfo(): array` | Полные настройки и возможности аккаунта. | [Описание](api/account-and-configuration.md#getaccountinfo) |
| `getSettings(): array` | Смысловой синоним чтения `/1.0/settings`. | [Описание](api/account-and-configuration.md#getsettings) |
| `getShippingPoints(): array` | Доступные точки сдачи и их возможности. | [Описание](api/account-and-configuration.md#getshippingpoints) |
| `getApiLimit(): array` | Сведения о лимите; сам вызов расходует запрос. | [Описание](api/account-and-configuration.md#getapilimit) |

## Заказы и получатели

| Метод | Краткое назначение | Глава |
| --- | --- | --- |
| `createOrders(array $orders): array` | Создание черновиков отправлений. | [Описание](api/orders.md#createorders) |
| `editOrder(Order\|array $order, int\|string $id): array` | Замена данных черновика. | [Описание](api/orders.md#editorder) |
| `findOrderById(int\|string $id): array` | Чтение по внутреннему идентификатору. | [Описание](api/orders.md#findorderbyid) |
| `findOrderByShopId(string $orderNumber): array` | Поиск по `order-num`. | [Описание](api/orders.md#findorderbyshopid) |
| `findOrderByRpo(string $rpo): array` | Поиск оформленного отправления по РПО. | [Описание](api/orders.md#findorderbyrpo) |
| `deleteOrders(array $orderIds): array` | Удаление новых черновиков. | [Описание](api/orders.md#deleteorders) |
| `returnOrdersToNew(array $orderIds): array` | Возврат заказов из партии в `NEW`. | [Описание](api/orders.md#returnorderstonew) |
| `getRecipientReliability(Recipient\|array $recipient): array` | Проверка одного получателя. | [Описание](api/orders.md#getrecipientreliability) |
| `getRecipientsReliability(array $recipients): array` | Пакетная проверка получателей. | [Описание](api/orders.md#getrecipientsreliability) |

## Партии и документы

| Метод | Краткое назначение | Глава |
| --- | --- | --- |
| `createBatch(array $orderIds, ?string $sendingDate = null, bool $useOnlineBalance = false): array` | Создание партии. | [Описание](api/batches-and-documents.md#createbatch) |
| `getAllBatches(...): array` | Список партий с фильтрами. | [Описание](api/batches-and-documents.md#getallbatches) |
| `getOrdersInBatch(...): array` | Состав партии. | [Описание](api/batches-and-documents.md#getordersinbatch) |
| `generateDocumentPackage(...): array` | ZIP-комплект печатных документов. | [Описание](api/batches-and-documents.md#generatedocumentpackage) |
| `generateDocumentF103(string $batchName): array` | Форма Ф103. | [Описание](api/batches-and-documents.md#generatedocumentf103) |

## Возвраты

| Метод | Краткое назначение | Глава |
| --- | --- | --- |
| `createReturnShipment(string $directBarcode, string $mailType = 'UNDEFINED'): array` | Возврат по прямому РПО. | [Описание](api/returns.md#createreturnshipment) |
| `createReturnShipments(array $returnShipments): array` | Отдельные возвраты без прямого РПО. | [Описание](api/returns.md#createreturnshipments) |
| `editReturnShipment(ReturnShipment\|array $returnShipment, string $rpo): array` | Изменение отдельного возврата. | [Описание](api/returns.md#editreturnshipment) |
| `deleteReturnShipment(string $rpo): array` | Удаление отдельного возврата. | [Описание](api/returns.md#deletereturnshipment) |

## Нормализация и тариф

| Метод | Краткое назначение | Глава |
| --- | --- | --- |
| `getTariff(int\|string $objectId, array $params, array $services = []): array` | Расчёт стоимости. | [Описание](api/normalization-and-tariffs.md#gettariff) |
| `getTariffAndDeliveryPeriod(...): array` | Расчёт стоимости и чтение срока. | [Описание](api/normalization-and-tariffs.md#gettariffanddeliveryperiod) |

Методы используют один и тот же маршрут. Нормализация адреса через `Request::postJson()` показана в той же главе.

## Справочники и отделения

| Метод | Краткое назначение | Глава |
| --- | --- | --- |
| `getCountryList(): array` | Встроенный справочник стран без сети. | [Описание](api/post-offices-and-dictionaries.md#getcountrylist) |
| `searchPostOfficeByIndex(...): array` | Сведения об отделении по индексу. | [Описание](api/post-offices-and-dictionaries.md#searchpostofficebyindex) |
| `searchPostOfficeByAddress(string $address, int $count = 3): array` | Подбор индексов по адресу. | [Описание](api/post-offices-and-dictionaries.md#searchpostofficebyaddress) |
| `searchPostOfficeByCoordinates(array $params): array` | Ближайшие отделения. | [Описание](api/post-offices-and-dictionaries.md#searchpostofficebycoordinates) |
| `getPostOfficeServices(int\|string $postalCode, ?string $serviceGroup = null): array` | Услуги отделения. | [Описание](api/post-offices-and-dictionaries.md#getpostofficeservices) |
| `getPostalCodesInLocality(string $locality, string $region = '', string $district = ''): array` | Индексы населённого пункта. | [Описание](api/post-offices-and-dictionaries.md#getpostalcodesinlocality) |

## SOAP-отслеживание

| Метод | Краткое назначение | Глава |
| --- | --- | --- |
| `getOperationsByRpo(string $rpo, string $lang = 'RUS'): array` | История одного РПО. | [Описание](api/tracking.md#getoperationsbyrpo) |
| `getNpayInfo(string $rpo, string $lang = 'RUS'): array` | События наложенного платежа. | [Описание](api/tracking.md#getnpayinfo) |
| `getTickets(array $rpoList, string $lang = 'RUS'): array` | Создание пакетных заявок. | [Описание](api/tracking.md#gettickets) |
| `getOperationsByTicket(string $ticket): array` | Чтение пакетного результата. | [Описание](api/tracking.md#getoperationsbyticket) |

## Дополнительный публичный слой

- [Сущности данных](entities-reference.md) — `fromArray()`, `toArray()`, обязательные поля и значения по умолчанию.
- [Низкоуровневый публичный интерфейс](low-level-api.md) — `CredentialsProvider`, `Request`, `SoapRequest`, `TrackingEntity`, `CountryDictionary`.
- [Наблюдаемые схемы реальных ответов](api-schemas/otpravka/README.md).
