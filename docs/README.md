# WT Otpravkapochtaru: документация

Документация относится к Joomla-пакету `WT Otpravkapochtaru` 3.x. Пакет не является самостоятельной заменой `lapaygroup/russianpost`: он настраивает эту SDK-библиотеку из параметров Joomla-плагина, предоставляет готовые Joomla Form поля и возвращает уже сконфигурированные клиенты для работы с API Почты России.

## Начало Работы

- [Корневой README](../README.md) - системные требования, установка и первый пример.
- [Настройка пакета в Joomla](joomla-user-guide.md) - параметры системного плагина и проверка подключения.
- [Архитектура тонкого фасада](thin-wrapper-architecture.md) - границы ответственности Joomla-обертки и SDK LapayGroup.

## API Для Разработчика

- [Публичный контракт фасада](public-api.md) - актуальные методы класса `Webtolk\Otpravkapochtaru\Otpravkapochtaru`.
- [Справочник методов фасада](facade-method-reference.md) - короткая сводка по текущей публичной поверхности.
- [Работа с SDK LapayGroup](developer-api.md) - примеры прямых вызовов через `otpravkaApi()`, `calculation()` и `trackingApi()`.
- [Сущности LapayGroup](entities-reference.md) - какие объекты SDK нужны для заказов, получателей и возвратов.
- [Низкоуровневый слой](low-level-api.md) - как фасад собирает транспорт и авторизацию.

## Тематические Разделы

- [Аккаунт и конфигурация](api/account-and-configuration.md)
- [Заказы](api/orders.md)
- [Партии и документы](api/batches-and-documents.md)
- [Возвраты](api/returns.md)
- [Тарифы и справочники расчета](api/normalization-and-tariffs.md)
- [ОПС и справочники](api/post-offices-and-dictionaries.md)
- [Трекинг](api/tracking.md)

## Снимки Ответов

- [JSON-снимки ответов API](api-snapshots/README.md)

Снимки в `api-snapshots/latest/` являются обезличенными примерами реальных ответов API. Они помогают понять форму данных, но не являются перечнем методов класса `Otpravkapochtaru`, потому что операции с API выполняются напрямую у провайдеров LapayGroup.
