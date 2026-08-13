# WT Otpravkapochtaru: документация

Документация относится к Joomla-пакету `WT Otpravkapochtaru` 3.x. Основная публичная точка входа для разработчика - фасад `Webtolk\Otpravkapochtaru\Otpravkapochtaru`.

## Начало работы

- [Корневой файл README](../README.md) - системные требования, установка, быстрый старт и первый пример.
- [Настройка пакета в Joomla](joomla-user-guide.md) - параметры системного плагина и проверка подключения.
- [Архитектура тонкой обертки](thin-wrapper-architecture.md) - как Joomla-пакет использует `lapaygroup/russianpost`.

## Интерфейс для разработчика

- [Публичный API фасада](public-api.md) - актуальный русский справочник по всем публичным методам библиотеки.
- [JSON-снимки ответов API](api-snapshots/README.md) - как были собраны реальные обезличенные ответы и ошибки.
- [Индекс снимков](api-snapshots/latest/index.json) - машинно-читаемый список всех зафиксированных методов.

## Исторические материалы

Файлы `developer-api.md`, `facade-method-reference.md`, `low-level-api.md`, `entities-reference.md` и документы в `api/` оставлены как вспомогательные материалы. Перед публикацией или копированием примеров сверяйте их с актуальным справочником [public-api.md](public-api.md), потому что публичный контракт версии 3.x строится вокруг фасада и библиотеки `lapaygroup/russianpost`.
