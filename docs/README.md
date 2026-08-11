# WT Otpravkapochtaru: документация

Документация относится к пакету `WT Otpravkapochtaru` версии 3.0.0 для Joomla 5+.

## Начало работы

- [Корневой README](../README.md) - установка, настройка и быстрый старт.
- [Пользовательская документация Joomla](joomla-user-guide.md) - параметры системного плагина и проверка подключения.
- [Техническая документация](developer-api.md) - исторический developer manual, который требует отдельной ревизии после перехода на thin wrapper.
- [Thin wrapper architecture](thin-wrapper-architecture.md) - актуальный контракт сборки Joomla-обертки и upstream SDK.

## Публичный интерфейс 3.0

- Основная точка входа: `Webtolk\Otpravkapochtaru\Otpravkapochtaru`.
- Настройки хранятся в системном плагине `wtotpravkapochtaru`.
- Joomla Form fields и web assets находятся в библиотеке.
- Низкоуровневые классы старого форка больше не являются публичным API пакета; используйте фасад и upstream SDK.

## Проверки

- Contract checks: `tests/Unit/Architecture/ThinWrapperContractTest.php`.
- Coverage сейчас статический: Composer requirements, installer SOAP policy, отсутствие удаленных namespace references в активных docs/runtime и smoke-check нового `dist/*.zip`, если он собран.

## Исторические материалы

Файлы в `docs/api/*`, `docs/entities-reference.md` и `docs/low-level-api.md` пока могут содержать примеры старого fork API. Перед публикацией полного developer manual их нужно обновить отдельной задачей.
