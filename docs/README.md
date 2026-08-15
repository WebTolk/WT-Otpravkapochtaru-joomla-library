# WT Otpravkapochtaru: документация

Документация относится к Joomla-пакету `WT Otpravkapochtaru` 3.x. Пакет не является самостоятельной заменой `lapaygroup/russianpost`: он настраивает эту SDK-библиотеку из параметров Joomla-плагина, предоставляет готовые Joomla Form поля и возвращает уже сконфигурированные клиенты для работы с API Почты России.

## Начало работы

- [Корневой README](../README.md) - системные требования, установка и первый пример.
- [Настройка пакета в Joomla](joomla-user-guide.md) - параметры системного плагина и проверка подключения.
- [Архитектура Joomla-фасада](Joomla-wrapper-architecture.md) - границы ответственности Joomla-обертки и SDK LapayGroup.

## API для разработчика

- [Публичный контракт фасада](public-api.md) - актуальные методы класса `Webtolk\Otpravkapochtaru\Otpravkapochtaru`.
- [Справочник методов фасада](facade-method-reference.md) - короткая сводка по текущей публичной поверхности.
- [Работа с SDK LapayGroup](developer-api.md) - примеры прямых вызовов через `otpravkaApi()` и `trackingApi()`; для тарифов аккаунта используется `OtpravkaApi::getDeliveryTariff()`.
- [Joomla Form поля](joomla-form-fields.md) - XML-синтаксис, параметры и связанные списки ОПС, типов и категорий отправлений.
- [Сущности LapayGroup](entities-reference.md) - какие объекты SDK нужны для заказов, получателей и возвратов.

## Тематические разделы

- [Аккаунт и конфигурация](api/account-and-configuration.md)
- [Заказы](api/orders.md)
- [Партии и документы](api/batches-and-documents.md)
- [Возвраты](api/returns.md)
- [Тарифы и справочники расчета](api/normalization-and-tariffs.md)
- [ОПС и справочники](api/post-offices-and-dictionaries.md)
- [Трекинг](api/tracking.md)

## Снимки ответов

- [JSON-снимки ответов API](api-snapshots/README.md)

JSON-снимки ответов API сервиса `Отправка` Почты России в `api-snapshots/latest/` обезличены и показывают структуру данных.
