# WT Otpravkapochtaru: документация

Документация относится к пакету `WT Otpravkapochtaru` версии 3.0.0 для Joomla 5+.

## Начало работы

- [Корневой README](../README.md) — установка, настройка и законченный быстрый старт с нормализацией и расчётом доставки.
- [Пользовательская документация Joomla](joomla-user-guide.md) — параметры системного плагина и проверка подключения.
- [Техническая документация](developer-api.md) — архитектура, конфигурация, формы данных, исключения, лимиты и безопасность.

## Публичный интерфейс

- [Карта всех методов фасада](facade-method-reference.md).
- [Аккаунт и настройки](api/account-and-configuration.md).
- [Нормализация и тариф](api/normalization-and-tariffs.md).
- [Заказы и получатели](api/orders.md).
- [Партии и документы](api/batches-and-documents.md).
- [Возвраты](api/returns.md).
- [Отделения и справочники](api/post-offices-and-dictionaries.md).
- [SOAP-отслеживание](api/tracking.md).

## Дополнительные классы

- [Сущности данных](entities-reference.md) — все публичные `fromArray()` и `toArray()`, обязательные поля и значения по умолчанию.
- [Низкоуровневый интерфейс](low-level-api.md) — `CredentialsProvider`, `Request`, `SoapRequest`, `TrackingEntity` и `CountryDictionary`.

## Реальные ответы

- [Наблюдаемые схемы ответов Otpravka REST API](api-schemas/otpravka/README.md).
- [Машиночитаемый индекс контрактов](api-schemas/otpravka/index.json).
- [Обезличенные примеры](api-schemas/otpravka/examples/).
- [JSON Schema Draft 2020-12](api-schemas/otpravka/schemas/).

Схемы построены по реальному прогону маршрута `410000 Саратов` → `685000 Магадан`. Они показывают наблюдаемую форму ответа, но не заменяют полную спецификацию внешнего API.
